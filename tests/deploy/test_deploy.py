"""Synthetic Linux tests; no application dependencies, real server or production data."""
import contextlib
import datetime as dt
import http.server
import importlib.util
import io
import json
import os
from pathlib import Path
import signal
import sqlite3
import subprocess
import sys
import tarfile
import tempfile
import threading
import unittest
from unittest.mock import patch
import urllib.request

SOURCE = Path(__file__).resolve().parents[2] / "ops/deploy"
spec = importlib.util.spec_from_file_location("deploy", SOURCE / "deploy.py")
d = importlib.util.module_from_spec(spec)
spec.loader.exec_module(d)
A, B, TREE = "a" * 40, "b" * 40, "c" * 40
WORKFLOWS = {".github/workflows/laravel.yml": {"id": 12, "jobs": ["regression"]},
             ".github/workflows/guarded-deploy.yml": {"id": 13, "jobs": ["deployment-guards", "effective-limits"]}}


def ci_fixture():
    data = {"/pulls/73": {"merged": True, "base": {"ref": "master", "repo": {"id": d.REPO_ID}},
            "head": {"sha": A, "repo": {"id": d.REPO_ID}}, "merge_commit_sha": B,
            "merged_by": {"login": "cholladay0816"}}}
    runs = []
    for index, (path, value) in enumerate(WORKFLOWS.items(), 1):
        runs.append({"id": index, "workflow_id": value["id"], "path": path, "head_sha": A,
            "event": "pull_request", "repository": {"id": d.REPO_ID},
            "head_repository": {"id": d.REPO_ID}, "status": "completed", "conclusion": "success",
            "run_number": 10, "run_attempt": 1})
        data[f"/actions/runs/{index}/jobs?filter=latest&per_page=100"] = {
            "total_count": len(value["jobs"]), "jobs": [{"name": name, "conclusion": "success"} for name in value["jobs"]]}
    data[f"/actions/runs?head_sha={A}&per_page=100"] = {"total_count": 2, "workflow_runs": runs}
    return data, {"head": A, "tree": TREE, "pr": 73, "compatible_without_migrations": True}


class CIGates(unittest.TestCase):
    def test_merged_sha_without_checks_uses_exact_approved_head(self):
        data, approval = ci_fixture()
        evidence = d.ci_gate(B, TREE, approval, WORKFLOWS, data.__getitem__)
        self.assertEqual([item["head"] for item in evidence], [A, A])

    def test_each_untrusted_or_incomplete_ci_scenario_stops(self):
        mutations = {
            "unmerged": lambda p, r, j: p.update(merged=False),
            "wrong base": lambda p, r, j: p["base"].update(ref="other"),
            "fork": lambda p, r, j: p["head"]["repo"].update(id=1),
            "changed head": lambda p, r, j: p["head"].update(sha=B),
            "unapproved merger": lambda p, r, j: p["merged_by"].update(login="someone"),
            "merge not candidate": lambda p, r, j: p.update(merge_commit_sha=A),
            "pending": lambda p, r, j: r.update(status="in_progress"),
            "failed": lambda p, r, j: r.update(conclusion="failure"),
            "wrong SHA": lambda p, r, j: r.update(head_sha=B),
            "wrong workflow": lambda p, r, j: r.update(workflow_id=99),
            "wrong path": lambda p, r, j: r.update(path=".github/workflows/fake.yml"),
            "wrong event": lambda p, r, j: r.update(event="pull_request_target"),
            "foreign run": lambda p, r, j: r["repository"].update(id=1),
            "skipped job": lambda p, r, j: j.update(conclusion="skipped"),
            "cancelled job": lambda p, r, j: j.update(conclusion="cancelled"),
        }
        for label, mutate in mutations.items():
            with self.subTest(label=label):
                data, approval = ci_fixture()
                mutate(data["/pulls/73"], data[f"/actions/runs?head_sha={A}&per_page=100"]["workflow_runs"][0],
                       data["/actions/runs/1/jobs?filter=latest&per_page=100"]["jobs"][0])
                with self.assertRaises(d.Stop):
                    d.ci_gate(B, TREE, approval, WORKFLOWS, data.__getitem__)

    def test_newer_failed_attempt_does_not_reuse_old_success(self):
        data, approval = ci_fixture()
        runs = data[f"/actions/runs?head_sha={A}&per_page=100"]["workflow_runs"]
        runs.append({**runs[0], "run_attempt": 2, "conclusion": "failure"})
        with self.assertRaises(d.Stop):
            d.ci_gate(B, TREE, approval, WORKFLOWS, data.__getitem__)

    def test_tree_compatibility_and_pagination_fail_closed(self):
        for change in ({"tree": A}, {"compatible_without_migrations": False}, {"head": "master"}):
            data, approval = ci_fixture()
            approval.update(change)
            with self.assertRaises(d.Stop):
                d.ci_gate(B, TREE, approval, WORKFLOWS, data.__getitem__)
        data, approval = ci_fixture()
        data[f"/actions/runs?head_sha={A}&per_page=100"]["total_count"] = 101
        with self.assertRaises(d.Stop):
            d.ci_gate(B, TREE, approval, WORKFLOWS, data.__getitem__)

    def test_api_failure_propagates_no_deployment(self):
        def offline(path):
            raise OSError("offline")
        with self.assertRaises(OSError):
            d.ci_gate(B, TREE, ci_fixture()[1], WORKFLOWS, offline)


class DailyGate(unittest.TestCase):
    def test_both_dst_transition_days_have_one_attempt(self):
        for date, hour in [("2026-03-08", 8), ("2026-11-01", 9), ("2026-07-15", 8), ("2026-12-15", 9)]:
            start = dt.datetime.fromisoformat(date).replace(tzinfo=dt.timezone.utc)
            attempted, eligible = None, []
            for minute in range(24 * 60):
                now = start + dt.timedelta(minutes=minute)
                day = d.due(now, attempted)
                if day:
                    # Ignore previous local calendar day at the start of the UTC interval.
                    attempted = day
                    if day == date:
                        eligible.append(now)
            self.assertEqual(eligible, [start + dt.timedelta(hours=hour, minutes=30)])

    def test_restart_catchup_and_attempt_marker(self):
        now = dt.datetime(2026, 10, 26, 20, tzinfo=dt.timezone.utc)
        self.assertEqual(d.due(now, None), "2026-10-26")
        self.assertIsNone(d.due(now, "2026-10-26"))
        self.assertIsNone(d.due(now.replace(hour=8), None))


class Filesystem(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.root = Path(self.tmp.name)
        self.old, self.new = self.root / "old", self.root / "new"
        for root in (self.old, self.new):
            (root / "database/migrations").mkdir(parents=True)
            (root / "database/migrations/2020_users.php").write_text("<?php // synthetic")
        self.db = self.root / "shared.sqlite"
        with sqlite3.connect(self.db) as db:
            db.executescript("CREATE TABLE migrations(migration TEXT); INSERT INTO migrations VALUES('2020_users'); CREATE TABLE writes(value TEXT);")

    def tearDown(self):
        self.tmp.cleanup()

    def test_migration_gate_preserves_db_bytes(self):
        original = self.db.read_bytes()
        d.migration_gate(self.new, self.old, self.db)
        self.assertEqual(self.db.read_bytes(), original)

    def test_pending_migration_stops_without_creation_or_schema_change(self):
        original = self.db.read_bytes()
        (self.new / "database/migrations/2021_extra.php").write_text("<?php")
        with self.assertRaises(d.Stop):
            d.migration_gate(self.new, self.old, self.db)
        self.assertEqual(self.db.read_bytes(), original)

    def test_edited_applied_migration_stops(self):
        (self.new / "database/migrations/2020_users.php").write_text("<?php // modified")
        with self.assertRaises(d.Stop):
            d.migration_gate(self.new, self.old, self.db)

    def test_unknown_or_missing_ledger_and_missing_database_stop(self):
        with sqlite3.connect(self.db) as db:
            db.execute("INSERT INTO migrations VALUES('unreviewed')")
        with self.assertRaises(d.Stop):
            d.migration_gate(self.new, self.old, self.db)
        missing = self.root / "never-created.sqlite"
        with self.assertRaises(d.Stop):
            d.migration_gate(self.new, self.old, missing)
        self.assertFalse(missing.exists())

    def test_atomic_metadata_and_control_permissions(self):
        path = self.root / "state.json"
        d.atomic_json(path, {"status": "ready"})
        self.assertEqual(d.read_json(path), {"status": "ready"})
        self.assertEqual(path.stat().st_mode & 0o777, 0o600)
        path.chmod(0o666)
        with self.assertRaises(d.Stop):
            d.read_json(path)
        link = self.root / "linked.json"
        link.symlink_to(path)
        with self.assertRaises(d.Stop):
            d.read_json(link)

    def test_capacity_stops_on_ram_and_disk_pressure(self):
        mem = self.root / "meminfo"
        mem.write_text("MemAvailable: 100000 kB\n")
        with self.assertRaises(d.Stop):
            d.capacity(self.root, mem)
        mem.write_text("MemAvailable: 551000 kB\n")
        with patch.object(d.shutil, "disk_usage", return_value=type("Disk", (), {"free": 1})()):
            with self.assertRaises(d.Stop):
                d.capacity(self.root, mem)

    def test_source_changes_detected_after_build(self):
        archive = self.root / "source.tar"
        source = self.root / "app.php"
        source.write_text("<?php // approved")
        with tarfile.open(archive, "w") as tar:
            tar.add(source, arcname="app.php")
        expected = d.source_fingerprint(archive)
        d.verify_source(self.root, expected)
        source.write_text("<?php // build tampered")
        with self.assertRaises(d.Stop):
            d.verify_source(self.root, expected)

    def test_unsafe_archive_links_and_paths_rejected(self):
        for name, kind in [("../escape", tarfile.REGTYPE), ("/absolute", tarfile.REGTYPE), ("link", tarfile.SYMTYPE)]:
            archive = self.root / "unsafe.tar"
            with tarfile.open(archive, "w") as tar:
                entry = tarfile.TarInfo(name)
                entry.type = kind
                tar.addfile(entry, io.BytesIO())
            with self.subTest(name=name), self.assertRaises(d.Stop):
                d.source_fingerprint(archive)

    def test_lock_excludes_another_process(self):
        child = "import importlib.util,sys,pathlib; s=importlib.util.spec_from_file_location('d',sys.argv[1]); d=importlib.util.module_from_spec(s); s.loader.exec_module(d)\nwith d.exclusive(pathlib.Path(sys.argv[2])) as acquired: print(int(acquired))"
        with d.exclusive(self.root) as acquired:
            self.assertTrue(acquired)
            result = subprocess.check_output([sys.executable, "-c", child, str(SOURCE / "deploy.py"), str(self.root)], text=True)
            self.assertEqual(result.strip(), "0")
        with d.exclusive(self.root) as acquired:
            self.assertTrue(acquired)


class Transactions(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.root = Path(self.tmp.name)
        self.state = self.root / "state"
        self.state.mkdir(mode=0o700)
        self.releases = self.root / "releases"
        self.releases.mkdir()
        self.old, self.new = self.releases / "old", self.releases / "new"
        for path, commit in [(self.old, A), (self.new, B)]:
            path.mkdir()
            d.atomic_json(path / ".adsr-release.json", {"commit": commit})
        self.current = self.root / "current"
        self.current.symlink_to(self.old)
        self.db = self.root / "persistent.sqlite"
        with sqlite3.connect(self.db) as db:
            db.execute("CREATE TABLE writes(value TEXT)")
        self.upload = self.root / "upload.txt"
        self.upload.write_text("accepted upload")
        d.atomic_json(self.state / "active.json", {"release": str(self.old), "commit": A})
        d.atomic_json(self.state / "preview-required.json", {"required": True})
        self.controller = d.Controller({"root": str(self.root), "build_user": "nobody", "mode": "preview"}, self.state, SOURCE)
        self.reloads = []
        self.controller.reload = lambda: self.reloads.append(self.current.resolve())
        fixture = self
        class Handler(http.server.BaseHTTPRequestHandler):
            def do_GET(self):
                release = fixture.current.resolve()
                body = json.dumps({"release": str(release), "commit": d.read_json(release / ".adsr-release.json")["commit"]}).encode()
                self.send_response(200)
                self.end_headers()
                self.wfile.write(body)
            def log_message(self, *args):
                pass
        self.server = http.server.ThreadingHTTPServer(("127.0.0.1", 0), Handler)
        self.thread = threading.Thread(target=self.server.serve_forever, daemon=True)
        self.thread.start()
        self.controller.health = self.health

    def tearDown(self):
        self.server.shutdown()
        self.server.server_close()
        self.thread.join()
        self.tmp.cleanup()

    def health(self, expected):
        with urllib.request.urlopen(f"http://127.0.0.1:{self.server.server_port}/", timeout=2) as response:
            body = json.load(response)
        d.require(body["release"] == str(expected), "stale Apache/OPcache release")

    def test_atomic_success_and_no_shared_path_replacement(self):
        self.controller.transaction(self.old, self.new, B, [{"run": 1}])
        self.assertEqual(self.current.resolve(), self.new)
        self.assertEqual(d.read_json(self.state / "active.json")["commit"], B)
        self.assertFalse((self.state / "transaction.json").exists())
        self.assertEqual(self.upload.read_text(), "accepted upload")

    def test_failed_health_restores_code_and_preserves_accepted_writes(self):
        def fail_new(expected):
            self.health(expected)
            if expected == self.new:
                with sqlite3.connect(self.db) as db:
                    db.execute("INSERT INTO writes VALUES('accepted during overlap')")
                self.upload.write_text("new upload during overlap")
                raise d.Stop("synthetic health failure")
        self.controller.health = fail_new
        with self.assertRaises(d.Stop):
            self.controller.transaction(self.old, self.new, B, [])
        self.assertEqual(self.current.resolve(), self.old)
        self.assertEqual(d.read_json(self.state / "active.json")["commit"], A)
        with sqlite3.connect(self.db) as db:
            self.assertEqual(db.execute("SELECT value FROM writes").fetchall(), [("accepted during overlap",)])
        self.assertEqual(self.upload.read_text(), "new upload during overlap")
        self.assertTrue(self.new.exists())  # No automatic failed-release cleanup.
        self.assertEqual(self.reloads, [self.new, self.old])

    def test_graceful_reload_failure_rolls_back(self):
        def reload():
            if self.current.resolve() == self.new:
                raise d.Stop("synthetic reload failure")
        self.controller.reload = reload
        with self.assertRaises(d.Stop):
            self.controller.transaction(self.old, self.new, B, [])
        self.assertEqual(self.current.resolve(), self.old)

    def test_failed_rollback_retains_recovery_journal(self):
        self.controller.health = lambda path: (_ for _ in ()).throw(d.Stop("synthetic failure"))
        with self.assertRaises(d.Stop):
            self.controller.transaction(self.old, self.new, B, [])
        self.assertTrue((self.state / "transaction.json").exists())

    def test_interrupted_switch_requires_explicit_recovery(self):
        d.atomic_json(self.state / "transaction.json", {"previous": str(self.old), "next": str(self.new), "commit": B})
        self.controller.switch(self.new)
        with self.assertRaises(d.Stop):
            self.controller.run()
        self.assertEqual(self.controller.recover()["commit"], A)
        self.assertEqual(self.current.resolve(), self.old)

    def test_committed_but_unfinished_transaction_recovers_new(self):
        d.atomic_json(self.state / "transaction.json", {"previous": str(self.old), "next": str(self.new), "commit": B})
        d.atomic_json(self.state / "active.json", {"release": str(self.new), "commit": B})
        self.controller.switch(self.new)
        self.assertEqual(self.controller.recover()["commit"], B)

    def test_no_change_does_one_ref_lookup_without_api_build_or_reload(self):
        calls = []
        def step(name, *args, **kwargs):
            calls.append(name)
            return A + "\trefs/heads/master"
        self.controller.step = step
        with patch.object(d, "api", side_effect=AssertionError("no API on unchanged source")):
            self.assertEqual(self.controller.run()["status"], "unchanged")
        self.assertEqual(calls, ["remote-master"])
        self.assertEqual(self.reloads, [])

    def test_preview_and_production_gates(self):
        app = {"key_present": True, "debug": False, "driver": "sqlite",
               "database": str(self.root / "shared/database/preview.sqlite"),
               "mailer": "array", "cookie": "adsr_php85_preview_session", "session": "file"}
        self.controller.validate_app(app)
        for key, value in [("mailer", "smtp"), ("debug", True), ("cookie", "production_session"), ("key_present", False)]:
            with self.subTest(key=key), self.assertRaises(d.Stop):
                self.controller.validate_app({**app, key: value})
        self.controller.c["mode"] = "production"
        with self.assertRaises(FileNotFoundError):
            self.controller.validate_app(app)
        d.atomic_json(self.state / "production-approved.json", {key: True for key in
            ("approved", "jobs_share_lock", "mail_ready", "queue_backlog_reviewed", "schedule_approved")})
        self.controller.validate_app({**app, "mailer": "smtp"})

    def test_main_daily_failure_latch_and_manual_clear(self):
        config = self.root / "config.json"
        d.atomic_json(config, {"root": "/srv/adsr-preview", "mode": "preview", "build_user": "adsr-build", "workflows": WORKFLOWS})
        with contextlib.ExitStack() as stack:
            for name, value in [("CONFIG", config), ("STATE", self.state), ("LIB", self.root)]:
                stack.enter_context(patch.object(d, name, value))
            stack.enter_context(patch.object(d, "Controller", return_value=self.controller))
            stack.enter_context(patch.object(sys, "argv", ["deploy.py", "daily"]))
            stack.enter_context(patch.object(d, "due", return_value="2026-10-26"))
            broken = stack.enter_context(patch.object(self.controller, "run", side_effect=d.Stop("synthetic failure")))
            self.assertEqual(d.main(), 1)
            self.assertEqual(d.read_json(self.state / "failure.json")["reason"], "synthetic failure")
            self.assertEqual(d.main(), 0)
            self.assertEqual(broken.call_count, 1)
            sys.argv = ["deploy.py", "run"]
            with self.assertRaises(d.Stop):
                d.main()
            sys.argv = ["deploy.py", "clear-failure"]
            self.assertEqual(d.main(), 0)
            self.assertFalse((self.state / "failure.json").exists())


class GitSource(unittest.TestCase):
    """A real synthetic Git merge graph; remote/API transport replaced, Git gates intact."""
    def setUp(self):
        Transactions.setUp(self)
        self.repo = self.root / 'source.git'
        self.repo.mkdir()
        self.git('init', '-b', 'master')
        self.git('config', 'user.email', 'synthetic@example.invalid')
        self.git('config', 'user.name', 'Synthetic test')
        (self.repo / 'code.txt').write_text('old')
        self.git('add', '.')
        self.git('commit', '-m', 'old')
        self.old_sha = self.git('rev-parse', 'HEAD')
        self.git('checkout', '-b', 'reviewed-head')
        (self.repo / 'code.txt').write_text('reviewed')
        self.git('commit', '-am', 'approved head')
        self.head_sha = self.git('rev-parse', 'HEAD')
        self.git('checkout', 'master')
        self.git('merge', '--no-ff', 'reviewed-head', '-m', 'owner merge')
        self.candidate = self.git('rev-parse', 'HEAD')
        self.git('branch', 'approved', self.head_sha)
        self.bind_metadata()

    health = Transactions.health
    tearDown = Transactions.tearDown

    def git(self, *args):
        result = subprocess.run(['git', '-C', str(self.repo), *args], text=True, capture_output=True)
        if result.returncode:
            raise d.Stop('synthetic Git gate refused command')
        return result.stdout.strip()

    def bind_metadata(self):
        tree = self.git('rev-parse', self.candidate + '^{tree}')
        d.atomic_json(self.old / '.adsr-release.json', {'commit': self.old_sha})
        d.atomic_json(self.new / '.adsr-release.json', {'commit': self.candidate})
        d.atomic_json(self.state / 'active.json', {'release': str(self.old), 'commit': self.old_sha})
        (self.state / 'approvals').mkdir(exist_ok=True)
        data, approval = ci_fixture()
        encoded = json.dumps([data, approval]).replace(A, self.head_sha).replace(B, self.candidate).replace(TREE, tree)
        self.data, approval = json.loads(encoded)
        d.atomic_json(self.state / 'approvals' / (self.candidate + '.json'), approval)
        self.controller.c['workflows'] = WORKFLOWS
        self.controller.step = lambda *args, **kwargs: self.candidate + '\trefs/heads/master'
        self.controller.git = lambda *args, **kwargs: None if args[0] == 'fetch' else self.git(*args)
        self.controller.prepare = lambda *args: self.new

    def test_real_merge_graph_and_equal_tree_can_deploy(self):
        original = d.ci_gate
        with patch.object(d, 'ci_gate', side_effect=lambda *args: original(*args, get=self.data.__getitem__)):
            result = self.controller.run()
        self.assertEqual(result['commit'], self.candidate)
        self.assertEqual(self.current.resolve(), self.new)

    def test_real_merged_tree_difference_stops_before_prepare(self):
        (self.repo / 'code.txt').write_text('untested merge resolution')
        self.git('commit', '-am', 'different merge tree')
        self.candidate = self.git('rev-parse', 'HEAD')
        self.bind_metadata()
        original = d.ci_gate
        with patch.object(d, 'ci_gate', side_effect=lambda *args: original(*args, get=self.data.__getitem__)):
            with self.assertRaisesRegex(d.Stop, 'merged tree differs'):
                self.controller.run()
        self.assertEqual(self.current.resolve(), self.old)

    def test_rewritten_master_ancestry_stops(self):
        self.git('checkout', '--orphan', 'unrelated')
        self.git('commit', '-am', 'rewritten root')
        self.candidate = self.git('rev-parse', 'HEAD')
        self.git('branch', '-f', 'master', self.candidate)
        self.bind_metadata()
        with self.assertRaises(d.Stop):
            self.controller.run()
        self.assertEqual(self.current.resolve(), self.old)


class Jobs(unittest.TestCase):
    def setUp(self):
        sys.modules['deploy'] = d
        spec = importlib.util.spec_from_file_location('jobs', SOURCE / 'jobs.py')
        self.jobs = importlib.util.module_from_spec(spec)
        spec.loader.exec_module(self.jobs)

    def test_preview_never_boots_app_or_drains_queue(self):
        with patch.object(d, 'read_json', return_value={'mode': 'preview'}), patch.object(d, 'Controller') as controller:
            self.assertEqual(self.jobs.main(), 0)
            controller.assert_not_called()

    def test_jobs_hold_same_exclusive_lock_and_preview_cannot_run(self):
        class Fake:
            c = {'mode': 'preview'}
        with self.assertRaises(d.Stop):
            self.jobs.run_jobs(Fake())

    def test_production_runs_schedule_then_bounded_database_queue(self):
        from unittest.mock import Mock
        controller = Mock()
        controller.c = {'mode': 'production'}
        controller.lib = SOURCE
        controller.validate_current.return_value = ({}, Path('/synthetic/release'))
        controller.step.side_effect = [json.dumps({'queue': 'database', 'failed_driver': 'database-uuids', 'retry_after': 90, 'timezone': 'UTC', 'pcntl': True}), None, None]
        self.jobs.run_jobs(controller)
        calls = controller.step.call_args_list
        self.assertEqual([call.args[0] for call in calls], ['jobs-preflight', 'schedule-run', 'drain-queue'])
        self.assertIn('--timeout=60', calls[2].args[1])
        self.assertIn('--stop-when-empty', calls[2].args[1])

    def test_sync_queue_or_missing_pcntl_stops_before_jobs(self):
        from unittest.mock import Mock
        for changes in ({'queue': 'sync'}, {'pcntl': False}, {'retry_after': 60}, {'timezone': 'America/Chicago'}):
            controller = Mock()
            controller.c = {'mode': 'production'}
            controller.lib = SOURCE
            controller.validate_current.return_value = ({}, Path('/synthetic/release'))
            app = {'queue': 'database', 'failed_driver': 'database-uuids', 'retry_after': 90, 'timezone': 'UTC', 'pcntl': True, **changes}
            controller.step.return_value = json.dumps(app)
            with self.assertRaises(d.Stop):
                self.jobs.run_jobs(controller)
            self.assertEqual(controller.step.call_count, 1)


if __name__ == "__main__":
    unittest.main()
