#!/usr/bin/python3
"""Installed, root-owned controller. Never execute this from a fetched release."""
from __future__ import annotations

import contextlib
import datetime as dt
import fcntl
import hashlib
import json
import os
from pathlib import Path
import pwd
import re
import shutil
import sqlite3
import stat
import subprocess
import sys
import tarfile
import time
import urllib.parse
import urllib.request
import uuid
from zoneinfo import ZoneInfo

REPO = "cholladay0816/American-Dad-Speedruns"
REPO_ID = 297477089
REMOTE = f"https://github.com/{REPO}.git"
STATE = Path("/var/lib/adsr-deploy")
LIB = Path("/usr/local/lib/adsr-deploy")
CONFIG = Path("/etc/adsr-deploy/config.json")
CAPS = {
    "MemoryMax": "384000000", "MemorySwapMax": "0", "CPUQuota": "100%",
    "TasksMax": "128", "RuntimeMaxSec": "600", "TimeoutStopSec": "5",
    "KillMode": "control-group", "OOMPolicy": "kill",
}
SHA = re.compile(r"[a-f0-9]{40}\Z")


class Stop(Exception):
    """Only safe, operator-facing messages belong here."""


def require(condition, message):
    if not condition:
        raise Stop(message)


def private(path):
    require(not path.is_symlink(), "root control path must not be a symlink")
    info = path.stat()
    require(info.st_uid == 0 and not info.st_mode & 0o022,
            "root control path must be root-owned and not group/world writable")


def read_json(path, maximum=65536):
    private(path)
    require(path.stat().st_size < maximum, "oversized control metadata")
    return json.loads(path.read_text())


def atomic_json(path, value):
    temporary = path.with_name(path.name + ".tmp-" + uuid.uuid4().hex)
    fd = os.open(temporary, os.O_WRONLY | os.O_CREAT | os.O_EXCL, 0o600)
    with os.fdopen(fd, "w") as stream:
        json.dump(value, stream, sort_keys=True)
        stream.write("\n")
        stream.flush()
        os.fsync(stream.fileno())
    os.replace(temporary, path)
    fd = os.open(path.parent, os.O_RDONLY | os.O_DIRECTORY)
    try:
        os.fsync(fd)
    finally:
        os.close(fd)


@contextlib.contextmanager
def exclusive(state):
    with (state / "operation.lock").open("a") as lock:
        os.chmod(lock.name, 0o600)
        try:
            fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        except BlockingIOError:
            yield False
            return
        yield True


def due(now, attempted_day):
    local = now.astimezone(ZoneInfo("America/Chicago"))
    day = local.date().isoformat()
    return day if (local.hour, local.minute) >= (3, 30) and day != attempted_day else None


def capacity(root, meminfo=Path("/proc/meminfo")):
    available = re.search(r"^MemAvailable:\s+(\d+) kB$", meminfo.read_text(), re.M)
    require(available and int(available[1]) * 1024 >= 480_000_000,
            "capacity: need 480000000 available RAM bytes; stop, do not raise limits")
    require(shutil.disk_usage(root).free >= 2_000_000_000,
            "capacity: need 2000000000 free disk bytes; operator retention review required")


def migration_gate(candidate, previous, database):
    def migrations(root):
        return {p.stem: hashlib.sha256(p.read_bytes()).hexdigest()
                for p in (root / "database/migrations").glob("*.php")}
    new, old = migrations(candidate), migrations(previous)
    require(new and new == old,
            "migration files changed: review schema and compatibility manually; no migrations run")
    require(database.is_file(), "shared database missing; never create or replace it")
    uri = "file:" + urllib.parse.quote(str(database), safe="/") + "?mode=ro"
    with sqlite3.connect(uri, uri=True, timeout=3) as connection:
        rows = connection.execute("SELECT migration FROM migrations LIMIT 1001").fetchall()
        require(len(rows) <= 1000, "oversized migration ledger; operator review required")
        applied = {row[0] for row in rows}
    require(applied == set(new),
            "pending/unknown migrations: operator must review ledger; no migrations run")


def source_fingerprint(archive):
    expected = {}
    with tarfile.open(archive) as stream:
        for member in stream:
            path = Path(member.name)
            require(not path.is_absolute() and ".." not in path.parts
                    and (member.isdir() or member.isfile()), "unsafe source archive entry")
            if member.isfile():
                require(member.size <= 20_000_000, "oversized source file")
                expected[member.name] = hashlib.sha256(stream.extractfile(member).read()).hexdigest()
    return expected


def verify_source(release, expected):
    for relative, digest in expected.items():
        path = release / relative
        require(path.is_file() and not path.is_symlink()
                and hashlib.sha256(path.read_bytes()).hexdigest() == digest,
                "release differs from approved source: " + relative)


def installed_source(release, commit):
    manifest = read_json(release / ".adsr-source.json", maximum=512000)
    require(manifest.get("commit") == commit and manifest.get("files"),
            "verified immutable baseline source manifest required")
    verify_source(release, manifest["files"])


def publish_assets(release, destination):
    """Append immutable generated assets; Apache Alias preserves both sides of rollback."""
    private(destination)
    require(destination.is_dir(), "reviewed shared Vite asset store required")
    gid = pwd.getpwnam("www-data").pw_gid
    source = release / "public/build/assets"
    require(source.is_dir() and not source.is_symlink(), "missing regular Vite assets")
    for asset in source.iterdir():
        require(asset.is_file() and not asset.is_symlink()
                and re.fullmatch(r"[A-Za-z0-9_.-]+\.(js|css|woff2?|ttf|png|jpe?g|svg|webp|gif|ico|map)", asset.name)
                and asset.stat().st_size <= 20_000_000, "unexpected Vite asset needs review")
        target = destination / asset.name
        if target.exists() or target.is_symlink():
            require(target.is_file() and not target.is_symlink()
                    and hashlib.sha256(target.read_bytes()).digest() == hashlib.sha256(asset.read_bytes()).digest(),
                    "immutable Vite asset collision")
        else:
            with target.open("xb") as stream:
                stream.write(asset.read_bytes())
                stream.flush()
                os.fsync(stream.fileno())
            os.chown(target, 0, gid)
            os.chmod(target, 0o640)


def remove_archive_placeholders(path):
    require(not path.is_symlink(), "unexpected source storage symlink")
    if not path.exists():
        return
    require(path.is_dir(), "unexpected source storage file")
    entries = list(path.rglob("*"))
    require(all(not item.is_symlink() and (item.is_dir() or
                (item.is_file() and item.name == ".gitignore")) for item in entries),
            "unexpected archive storage content")
    for item in sorted(entries, key=lambda item: len(item.parts), reverse=True):
        item.rmdir() if item.is_dir() else item.unlink()
    path.rmdir()


def api(path):
    require(path.startswith("/") and ".." not in path, "invalid API path")
    request = urllib.request.Request("https://api.github.com/repos/" + REPO + path,
        headers={"Accept": "application/vnd.github+json", "User-Agent": "adsr-guarded-deploy",
                 "X-GitHub-Api-Version": "2022-11-28"})
    with urllib.request.urlopen(request, timeout=15) as response:
        require(response.status == 200 and response.url.startswith("https://api.github.com/"),
                "untrusted GitHub API response")
        body = response.read(2_000_001)
    require(len(body) <= 2_000_000, "GitHub response too large; fail closed")
    return json.loads(body)


def ci_gate(candidate, tree, approval, workflows, get=api):
    head = approval.get("head", "")
    require(SHA.fullmatch(head) and approval.get("tree") == tree,
            "approval head/tree do not match immutable source")
    require(approval.get("compatible_without_migrations") is True,
            "approval must explicitly cover old/new overlap and write-preserving rollback")
    number = approval.get("pr")
    require(type(number) is int and number > 0, "approval needs exact reviewed PR number")
    pr = get(f"/pulls/{number}")
    require(pr.get("merged") is True and pr.get("base", {}).get("ref") == "master"
            and pr.get("base", {}).get("repo", {}).get("id") == REPO_ID
            and pr.get("head", {}).get("repo", {}).get("id") == REPO_ID
            and pr.get("head", {}).get("sha") == head
            and pr.get("merge_commit_sha") == candidate
            and pr.get("merged_by", {}).get("login") == "cholladay0816",
            "candidate must be the owner-merged PR commit on trusted master")
    runs = get(f"/actions/runs?head_sha={head}&per_page=100")
    require(runs.get("total_count", 101) <= 100, "too many CI runs; operator review required")
    evidence = []
    for path, expected in workflows.items():
        require(type(expected["id"]) is int and expected["id"] > 0, "configure verified workflow IDs")
        matches = [r for r in runs.get("workflow_runs", [])
                   if r.get("workflow_id") == expected["id"] and r.get("path") == path
                   and r.get("head_sha") == head and r.get("event") == "pull_request"
                   and r.get("repository", {}).get("id") == REPO_ID
                   and r.get("head_repository", {}).get("id") == REPO_ID]
        require(matches, "missing exact approved-head workflow: " + path)
        run = max(matches, key=lambda r: (r["run_number"], r["run_attempt"], r["id"]))
        require(run.get("status") == "completed" and run.get("conclusion") == "success",
                "latest exact-head CI is not successful: " + path)
        jobs = get(f'/actions/runs/{run["id"]}/jobs?filter=latest&per_page=100')
        require(jobs.get("total_count", 101) <= 100, "too many CI jobs")
        for name in expected["jobs"]:
            selected = [j for j in jobs.get("jobs", []) if j.get("name") == name]
            require(len(selected) == 1 and selected[0].get("conclusion") == "success",
                    "required CI job missing, skipped or failed: " + name)
        evidence.append({"run": run["id"], "attempt": run["run_attempt"], "head": head})
    return evidence


class Controller:
    def __init__(self, config, state=STATE, lib=LIB):
        self.c, self.state, self.lib = config, state, lib
        self.root = Path(config["root"])
        self.releases = self.root / "releases"
        self.repo = self.root / "source.git"
        self.current = self.root / "current"
        self.shared = self.root / "shared"
        self.build_user = config["build_user"]
        self.web_user = "www-data"
        self.run_id = dt.datetime.now(dt.timezone.utc).strftime("%Y%m%dT%H%M%SZ") + "-" + uuid.uuid4().hex[:8]
        self.log = state / "run.log"

    def step(self, name, argv, user=None, cwd=None, env=None, capture=False, reserve=True):
        if reserve:
            capacity(self.root)
        unit = "adsr-deploy-" + uuid.uuid4().hex
        argv = [str(a) for a in argv]
        command = ["/usr/bin/systemd-run", "--quiet", "--wait", "--pipe", "--collect",
                   "--service-type=exec", "--unit=" + unit,
                   "--property=User=" + (user or self.build_user),
                   "--property=NoNewPrivileges=yes", "--property=UMask=0027",
                   "--property=LimitFSIZE=2147483648"]
        command += ["--property=" + k + "=" + v for k, v in CAPS.items()]
        if cwd:
            command.append("--working-directory=" + str(cwd))
        base_env = {"PATH": "/usr/local/bin:/usr/bin:/bin", "LANG": "C.UTF-8"}
        base_env.update(env or {})
        command += ["--setenv=" + k + "=" + v for k, v in base_env.items()]
        command += ["/usr/bin/python3", str(self.lib / "caps_guard.py"), unit + ".service", *argv]
        with self.log.open("ab", buffering=0) as log:
            os.chmod(self.log, 0o600)
            log.write(("\n" + self.run_id + " step=" + name + "\n").encode())
            output = self.state / "step-output"
            with output.open("wb") as result:
                os.chmod(output, 0o600)
                try:
                    process = subprocess.run(command, stdout=result if capture else log,
                                             stderr=log, timeout=620, check=False)
                except BaseException:
                    # systemd owns descendants; stop the entire cgroup even if the client hung.
                    subprocess.run(["/usr/bin/systemctl", "stop", unit], timeout=15, check=False,
                                   stdout=log, stderr=log)
                    raise Stop("step client timeout: " + name)
            require(process.returncode == 0, "step failed: " + name + "; inspect root-private run.log")
            if capture:
                require(output.stat().st_size <= 65536, "oversized step result")
                return output.read_text().strip()

    def git(self, *args, capture=True):
        return self.step("git", ["/usr/bin/git", "-c", "core.hooksPath=/dev/null",
            "-c", "protocol.file.allow=never", "-c", "credential.helper=", "--git-dir=" + str(self.repo), *args],
            env={"GIT_CONFIG_NOSYSTEM": "1", "GIT_CONFIG_GLOBAL": "/dev/null",
                 "GIT_TERMINAL_PROMPT": "0", "GIT_NO_REPLACE_OBJECTS": "1"}, capture=capture)

    def orphan_gate(self):
        result = subprocess.run(["/usr/bin/systemctl", "list-units", "adsr-deploy-*",
            "--state=activating,active,deactivating", "--plain", "--no-legend", "--no-pager"],
            capture_output=True, timeout=10, text=True, check=True)
        require(not result.stdout.strip(),
                "orphan deployment process still active: inspect/stop its cgroup before another operation")

    def validate_current(self):
        active = read_json(self.state / "active.json")
        require(not active.get("overlay"),
                "unmerged preview overlay present: reconcile exact source provenance before automation")
        release = Path(active["release"])
        require(SHA.fullmatch(active.get("commit", "")), "invalid active commit")
        require(self.current.is_symlink() and self.current.resolve() == release.resolve()
                and release.parent == self.releases and not release.is_symlink(),
                "current/active mismatch: recover interrupted transaction before continuing")
        require(read_json(release / ".adsr-release.json")["commit"] == active["commit"],
                "active release identity mismatch")
        require(not read_json(release / ".adsr-release.json").get("overlay"),
                "release overlay present: reconcile source against trusted merged master")
        installed_source(release, active["commit"])
        return active, release

    def validate_mode(self):
        gate = read_json(self.state / "preview-required.json")
        require(type(gate.get("required")) is bool, "explicit persistent preview gate required")
        if self.c["mode"] == "production":
            require(gate["required"] is False, "preview gate still required; owner cutover approval absent")
            approval = read_json(self.state / "production-approved.json")
            require(all(approval.get(key) is True for key in
                    ("approved", "jobs_share_lock", "mail_ready", "queue_backlog_reviewed", "schedule_approved")),
                    "production mode requires explicit cutover and scheduler/queue audit approval")
        else:
            require(gate["required"] is True, "preview mode must retain the persistent preview gate")

    def health(self, release):
        expected = read_json(release / ".adsr-release.json")
        for _ in range(5):
            try:
                value = self.step("http-health", ["/usr/bin/curl", "--silent", "--show-error", "--fail",
                    "--noproxy", "*", "--connect-timeout", "2", "--max-time", "8",
                    "--resolve", "3.89.97.195:443:127.0.0.1", "--cacert", self.c["tls_ca"],
                    "https://3.89.97.195/_adsr-health.php"], user=self.web_user, capture=True, reserve=False)
                require(json.loads(value) == {"ok": True, "commit": expected["commit"],
                                               "release": str(release)}, "unexpected HTTP release identity")
                return
            except (Stop, ValueError):
                time.sleep(1)
        raise Stop("HTTP health failed: check Apache, PHP, DB and certificate (no renewal performed)")

    def reload(self):
        self.step("apache-configtest", ["/usr/sbin/apache2ctl", "configtest"], user="root", reserve=False)
        self.step("apache-graceful", ["/usr/bin/systemctl", "reload", "apache2"], user="root", reserve=False)

    def switch(self, release):
        temporary = self.root / (".current-" + uuid.uuid4().hex)
        temporary.symlink_to(release, target_is_directory=True)
        os.replace(temporary, self.current)
        fd = os.open(self.root, os.O_RDONLY | os.O_DIRECTORY)
        try:
            os.fsync(fd)
        finally:
            os.close(fd)

    def transaction(self, previous, target, commit, evidence):
        journal = self.state / "transaction.json"
        old_active = read_json(self.state / "active.json")
        atomic_json(journal, {"previous": str(previous), "next": str(target), "commit": commit})
        try:
            self.switch(target)
            self.reload()
            self.health(target)
            atomic_json(self.state / "active.json", {"commit": commit, "release": str(target),
                        "ci": evidence, "at": self.run_id})
        except BaseException:
            # Code rollback only. Shared DB/uploads/sessions are never restored or replaced.
            self.switch(previous)
            self.reload()
            self.health(previous)
            atomic_json(self.state / "active.json", old_active)
            journal.unlink()
            raise
        journal.unlink()

    def permissions(self, release):
        gid = pwd.getpwnam(self.web_user).pw_gid
        for base, directories, files in os.walk(release, followlinks=False):
            for path in [Path(base), *(Path(base) / x for x in directories + files)]:
                if path.is_symlink():
                    os.lchown(path, 0, gid)
                else:
                    os.chown(path, 0, gid)
                    os.chmod(path, 0o750 if path.is_dir() else 0o640)
        for path in [release / "bootstrap/cache", release / "storage/framework"]:
            for base, _, files in os.walk(path, followlinks=False):
                os.chown(base, 0, gid)
                os.chmod(base, 0o770)
                for name in files:
                    item = Path(base) / name
                    if not item.is_symlink():
                        os.chown(item, 0, gid)
                        os.chmod(item, 0o660)

    def prepare(self, candidate, previous, tree):
        release = self.releases / (candidate + "-" + self.run_id)
        release.mkdir(mode=0o750)
        build = pwd.getpwnam(self.build_user)
        os.chown(release, build.pw_uid, build.pw_gid)
        self.git("archive", "--format=tar", "--output=" + str(release / "source.tar"), candidate, capture=False)
        expected = source_fingerprint(release / "source.tar")
        require(not any(name in expected for name in
                (".env", ".adsr-release.json", ".adsr-source.json", "public/_adsr-health.php", "source.tar")),
                "source contains reserved deployment artifacts")
        self.step("extract", ["/usr/bin/tar", "--extract", "--file=source.tar", "--no-same-owner",
                             "--no-same-permissions"], cwd=release)
        verify_source(release, expected)
        require(not (release / "vendor").exists() and not (release / ".env").exists(),
                "source must not contain vendor or environment")
        package = json.loads((release / "package.json").read_text())
        composer = json.loads((release / "composer.json").read_text())
        require(package.get("scripts", {}).get("build") == "vite build", "review frontend build command")
        require(composer.get("scripts", {}).get("post-autoload-dump") == [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump", "@php artisan package:discover --ansi"],
            "review changed Composer post-autoload commands")
        home = release / ".build-home"
        home.mkdir(mode=0o700)
        os.chown(home, build.pw_uid, build.pw_gid)
        environment = {"HOME": str(home), "NODE_OPTIONS": "--max-old-space-size=192",
                       "COMPOSER_HOME": str(home / "composer"), "COMPOSER_MEMORY_LIMIT": "224M",
                       "COMPOSER_MAX_PARALLEL_HTTP": "1", "COMPOSER_MAX_PARALLEL_PROCESSES": "1"}
        require(self.step("node-version", ["/usr/local/bin/node", "--version"], cwd=release,
                          env=environment, capture=True) == "v26.3.0", "unreviewed Node runtime")
        require(self.step("composer-version", ["/usr/local/bin/composer", "--no-ansi", "--version"],
                          cwd=release, env=environment, capture=True).startswith("Composer version 2.8.12 "),
                "unreviewed Composer runtime")
        self.step("npm-ci", ["/usr/local/bin/npm", "ci", "--include=dev", "--ignore-scripts", "--no-audit", "--no-fund"], cwd=release, env=environment)
        self.step("npm-build", ["/usr/local/bin/npm", "run", "build", "--ignore-scripts"], cwd=release, env=environment)
        self.step("composer-install", ["/usr/local/bin/composer", "install", "--no-dev", "--prefer-dist",
            "--optimize-autoloader", "--no-scripts", "--no-plugins", "--no-interaction"], cwd=release, env=environment)
        self.step("platform", ["/usr/local/bin/composer", "check-platform-reqs", "--no-dev", "--no-plugins"], cwd=release, env=environment)
        verify_source(release, expected)
        migration_gate(release, previous, self.shared / "database/preview.sqlite")
        # Only link persistent locations after builds have finished. No source command runs as root.
        links = {".env": self.shared / ".env", "storage/app": self.shared / "storage/app",
                 "storage/logs": self.shared / "storage/logs",
                 "storage/framework/sessions": self.shared / "storage/framework/sessions",
                 "public/storage": self.shared / "storage/app/public"}
        for relative, destination in links.items():
            path = release / relative
            require(destination.exists(), "missing persistent location: " + relative)
            if relative != ".env" and (path.exists() or path.is_symlink()):
                # Newly extracted nested .gitignore placeholders only; never persistent data.
                remove_archive_placeholders(path)
            require(not path.exists() and not path.is_symlink(), "unexpected source link target")
            path.symlink_to(destination, target_is_directory=destination.is_dir())
        for relative in ["storage/framework/cache/data", "storage/framework/views", "bootstrap/cache"]:
            (release / relative).mkdir(parents=True, exist_ok=True)
        shutil.copyfile(self.lib / "health.php", release / "public/_adsr-health.php")
        atomic_json(release / ".adsr-release.json", {"commit": candidate, "tree": tree})
        atomic_json(release / ".adsr-source.json", {"commit": candidate, "files": {
            name: digest for name, digest in expected.items() if not name.startswith("storage/")}})
        self.permissions(release)
        config = self.step("effective-app-config", ["/usr/bin/php8.5", self.lib / "preflight.php"],
                           user=self.web_user, cwd=release, capture=True)
        self.validate_app(json.loads(config))
        self.step("reviewed-post-autoload", ["/usr/local/bin/composer", "--no-plugins", "run-script",
            "post-autoload-dump", "--no-dev", "--no-interaction"], user=self.web_user, cwd=release,
            env={"COMPOSER_MEMORY_LIMIT": "224M", "COMPOSER_MAX_PARALLEL_HTTP": "1",
                 "COMPOSER_MAX_PARALLEL_PROCESSES": "1", "HOME": "/nonexistent"})
        migration_gate(release, previous, self.shared / "database/preview.sqlite")
        publish_assets(release, self.shared / "vite-assets")
        return release

    def validate_app(self, app):
        require(app.get("key_present") is True and app.get("debug") is False,
                "APP_KEY must already exist and APP_DEBUG must be false")
        require(app.get("database") == str(self.shared / "database/preview.sqlite")
                and app.get("driver") == "sqlite", "effective DB must be the preserved shared SQLite file")
        if self.c["mode"] == "preview":
            require(app.get("mailer") == "array" and app.get("cookie") == "adsr_php85_preview_session"
                    and app.get("session") in ("file", "database"),
                    "preview must retain array mail and isolated persistent sessions")
        else:
            approval = read_json(self.state / "production-approved.json")
            require(all(approval.get(key) is True for key in
                    ("approved", "jobs_share_lock", "mail_ready", "queue_backlog_reviewed", "schedule_approved")),
                    "production mode requires explicit cutover and scheduler/queue audit approval")

    def run(self):
        self.validate_mode()
        require(not (self.state / "transaction.json").exists(),
                "interrupted switch: inspect transaction.json and use recover; no automatic DB restore")
        active, previous = self.validate_current()
        # One bounded remote ref lookup; no GitHub API, dependency work or reload on no change.
        remote = self.step("remote-master", ["/usr/bin/git", "-c", "credential.helper=",
            "ls-remote", "--exit-code", REMOTE, "refs/heads/master"], capture=True)
        candidate = remote.split()[0]
        require(SHA.fullmatch(candidate), "invalid remote master commit")
        if candidate == active["commit"]:
            return {"status": "unchanged", "commit": candidate}
        require(self.repo.is_dir(), "missing initialized source.git; installer preparation required")
        self.git("fetch", "--no-tags", "--prune", REMOTE, "+refs/heads/master:refs/heads/master", capture=False)
        self.git("fsck", "--strict", "--no-reflogs", capture=False)
        require(self.git("rev-parse", "refs/heads/master") == candidate, "master moved during fetch; retry after review")
        self.git("merge-base", "--is-ancestor", active["commit"], candidate, capture=False)
        tree = self.git("rev-parse", candidate + "^{tree}")
        approval_file = self.state / "approvals" / (candidate + ".json")
        require(approval_file.is_file(), "master has no operator approval: " + candidate + "; review exact PR head/tree")
        approval = read_json(approval_file)
        evidence = ci_gate(candidate, tree, approval, self.c["workflows"])
        head = approval["head"]
        self.git("fetch", "--no-tags", REMOTE, f'refs/pull/{approval["pr"]}/head:refs/heads/approved', capture=False)
        require(self.git("rev-parse", "refs/heads/approved") == head, "approved PR head changed")
        self.git("merge-base", "--is-ancestor", head, candidate, capture=False)
        require(self.git("rev-parse", head + "^{tree}") == tree, "merged tree differs from tested approved head")
        modes = self.git("ls-tree", "-r", candidate)
        require(not any(line.startswith(("120000", "160000")) for line in modes.splitlines()),
                "source links/submodules need explicit deployment review")
        self.health(previous)
        release = self.prepare(candidate, previous, tree)
        self.transaction(previous, release, candidate, evidence)
        return {"status": "deployed", "commit": candidate, "release": str(release), "ci": evidence}

    def recover(self):
        journal = read_json(self.state / "transaction.json")
        previous = Path(journal["previous"])
        active = read_json(self.state / "active.json")
        # If metadata already committed, complete that health check; otherwise restore old code.
        chosen = Path(active["release"])
        require(chosen in (previous, Path(journal["next"])) and chosen.parent == self.releases,
                "ambiguous transaction requires operator inspection")
        self.switch(chosen)
        self.reload()
        self.health(chosen)
        (self.state / "transaction.json").unlink()
        return {"status": "recovered", "commit": active["commit"], "release": str(chosen)}


def main():
    os.umask(0o077)
    require(os.geteuid() == 0, "controller requires the reviewed root installation")
    private(CONFIG)
    private(STATE)
    private(LIB)
    config = read_json(CONFIG)
    require(config.get("root") == "/srv/adsr-preview" and config.get("mode") in ("preview", "production"),
            "only the reviewed new preview target is supported")
    require(config.get("build_user") == "adsr-build", "dedicated unprivileged adsr-build account required")
    require(len(config.get("workflows", {})) == 2, "both reviewed CI workflows are required")
    controller = Controller(config)
    action = sys.argv[1] if len(sys.argv) == 2 else ""
    require(action in ("daily", "run", "recover", "clear-failure"), "use daily, run, recover or clear-failure")
    with exclusive(STATE) as acquired:
        if not acquired:
            return 0
        if action == "clear-failure":
            controller.orphan_gate()
            require(not (STATE / "transaction.json").exists(), "recover interrupted switch before clearing latch")
            controller.validate_current()
            controller.health(Path(read_json(STATE / "active.json")["release"]))
            (STATE / "failure.json").unlink(missing_ok=True)
            return 0
        if action == "daily":
            dayfile = STATE / "daily.json"
            day = due(dt.datetime.now(dt.timezone.utc), read_json(dayfile).get("day") if dayfile.exists() else None)
            if not day or (STATE / "failure.json").exists():
                return 0
            atomic_json(dayfile, {"day": day})
        elif action != "recover":
            require(not (STATE / "failure.json").exists(), "failure latch set; inspect and clear-failure after review")
        try:
            controller.orphan_gate()
            result = controller.recover() if action == "recover" else controller.run()
            atomic_json(STATE / "last-result.json", {**result, "at": controller.run_id})
        except Exception as error:
            # Never echo exception details, command output, environment, DB records or HTTP bodies.
            reason = str(error) if isinstance(error, Stop) else "internal failure; inspect private log and installation"
            failure = {"status": "failed", "reason": reason, "at": controller.run_id}
            atomic_json(STATE / "failure.json", failure)
            atomic_json(STATE / "last-result.json", failure)
            return 1
    return 0


if __name__ == "__main__":
    try:
        sys.exit(main())
    except Exception:
        # Cron has MAILTO empty; diagnostics must stay local and secret-free.
        sys.exit(1)
