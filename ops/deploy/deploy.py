#!/usr/bin/python3
"""Installed, root-owned controller. Never execute this from a fetched release."""
from __future__ import annotations

import contextlib
import datetime as dt
import fcntl
import hashlib
import json
import os
import posixpath
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
MAX_SOURCE_FILE = 20_000_000
HASH_CHUNK = 65536


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


def schema_fingerprint(database):
    require(database.is_file(), "shared database missing; never create or replace it")
    uri = "file:" + urllib.parse.quote(str(database), safe="/") + "?mode=ro"
    with sqlite3.connect(uri, uri=True, timeout=3) as connection:
        rows = connection.execute("SELECT type,name,tbl_name,substr(sql,1,65537) FROM sqlite_master "
            "WHERE name NOT GLOB 'sqlite_*' ORDER BY type,name,tbl_name LIMIT 257").fetchall()
    require(len(rows) <= 256 and all(all(value is None or len(value) <= 65536 for value in row) for row in rows),
            "oversized schema metadata; operator review required")
    return hashlib.sha256(json.dumps(rows, separators=(",", ":")).encode()).hexdigest()


def migration_gate(candidate, previous, database, approved_schema=None):
    def migrations(root):
        return {p.stem: fingerprint_file(root, str(p.relative_to(root)))
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
    if approved_schema is not None:
        require(re.fullmatch(r"[a-f0-9]{64}", approved_schema)
                and schema_fingerprint(database) == approved_schema,
                "database schema drift from reviewed baseline; stop for operator review, no migrations run")


def source_fingerprint(archive):
    expected = {}
    with tarfile.open(archive) as stream:
        for index, member in enumerate(stream):
            require(index < 8192, "too many source archive entries")
            path = Path(member.name)
            require(not path.is_absolute() and ".." not in path.parts
                    and (member.isdir() or member.isfile()), "unsafe source archive entry")
            if member.isfile():
                require(member.size <= MAX_SOURCE_FILE and member.name not in expected
                        and len(expected) < 4096, "oversized/duplicate source archive entry")
                with stream.extractfile(member) as content:
                    expected[member.name] = hash_stream(content, member.size)
    return expected


def hash_stream(stream, expected_size):
    require(type(expected_size) is int and 0 <= expected_size <= MAX_SOURCE_FILE, "oversized source file")
    digest, size = hashlib.sha256(), 0
    while True:
        chunk = stream.read(min(HASH_CHUNK, expected_size - size + 1))
        if not chunk:
            break
        size += len(chunk)
        require(size <= expected_size, "source grew during bounded hashing")
        digest.update(chunk)
    require(size == expected_size, "source size differs from approved archive")
    return {"sha256": digest.hexdigest(), "size": size}


@contextlib.contextmanager
def file_beneath(root, relative):
    """Never follow a link in any component, including after an earlier lstat."""
    path = Path(relative)
    require(not path.is_absolute() and path.parts and ".." not in path.parts, "invalid source path")
    directory = os.open(root, os.O_RDONLY | os.O_DIRECTORY | os.O_NOFOLLOW)
    descriptor = None
    try:
        for part in path.parts[:-1]:
            child = os.open(part, os.O_RDONLY | os.O_DIRECTORY | os.O_NOFOLLOW, dir_fd=directory)
            os.close(directory)
            directory = child
        descriptor = os.open(path.name, os.O_RDONLY | os.O_NOFOLLOW | os.O_NONBLOCK, dir_fd=directory)
        info = os.fstat(descriptor)
        require(stat.S_ISREG(info.st_mode) and info.st_nlink == 1, "unexpected source type or hard link")
        with os.fdopen(descriptor, "rb") as stream:
            descriptor = None
            yield stream, info
    except OSError:
        raise Stop("unsafe or missing source path: " + relative)
    finally:
        if descriptor is not None:
            os.close(descriptor)
        os.close(directory)


def fingerprint_file(root, relative, expected_size=None):
    with file_beneath(root, relative) as (stream, before):
        require(before.st_size <= MAX_SOURCE_FILE and
                (expected_size is None or before.st_size == expected_size),
                "source size differs from approved archive: " + relative)
        result = hash_stream(stream, before.st_size)
        after = os.fstat(stream.fileno())
        require((before.st_size, before.st_mtime_ns, before.st_ctime_ns) ==
                (after.st_size, after.st_mtime_ns, after.st_ctime_ns), "source changed during verification")
        return result


def small_source_json(root, relative):
    with file_beneath(root, relative) as (stream, info):
        require(info.st_size <= 65536, "oversized source configuration")
        return json.loads(stream.read(65537))


def verify_source(release, expected):
    for relative, record in expected.items():
        require(isinstance(record, dict) and set(record) == {"sha256", "size"}
                and re.fullmatch(r"[a-f0-9]{64}", record["sha256"]), "size-bearing source manifest required")
        require(fingerprint_file(release, relative, record["size"]) == record,
                "release differs from approved source: " + relative)


def installed_source(release, commit):
    manifest = read_json(release / ".adsr-source.json", maximum=512000)
    require(manifest.get("commit") == commit and manifest.get("files"),
            "verified immutable baseline source manifest required")
    verify_source(release, manifest["files"])


def publish_assets(release, destination):
    """Append immutable generated assets; Apache Alias preserves both sides of rollback."""
    private(destination)
    private(destination.parent)
    require(destination.is_dir(), "reviewed shared Vite asset store required")
    gid = pwd.getpwnam("www-data").pw_gid
    source = release / "public/build/assets"
    require(source.is_dir() and not source.is_symlink(), "missing regular Vite assets")
    total = 0
    for index, asset in enumerate(source.iterdir()):
        require(index < 512, "too many generated Vite assets")
        require(re.fullmatch(r"[A-Za-z0-9_.-]+\.(js|css|woff2?|ttf|png|jpe?g|svg|webp|gif|ico|map)", asset.name),
                "unexpected Vite asset needs review")
        record = fingerprint_file(source, asset.name)
        total += record["size"]
        require(total <= 64_000_000, "generated Vite assets exceed reviewed 64000000 byte budget")
        directory = os.open(destination, os.O_RDONLY | os.O_DIRECTORY | os.O_NOFOLLOW)
        try:
            info = os.fstat(directory)
            require(info.st_uid == 0 and not info.st_mode & 0o022, "unsafe shared asset directory")
            try:
                output = os.open(asset.name, os.O_WRONLY | os.O_CREAT | os.O_EXCL | os.O_NOFOLLOW,
                                 0o640, dir_fd=directory)
            except FileExistsError:
                require(fingerprint_file(destination, asset.name, record["size"]) == record,
                        "immutable Vite asset collision")
                continue
            with os.fdopen(output, "wb") as stream, file_beneath(source, asset.name) as (content, _):
                shutil.copyfileobj(content, stream, length=HASH_CHUNK)
                stream.flush()
                os.fsync(stream.fileno())
                os.fchown(stream.fileno(), 0, gid)
                os.fchmod(stream.fileno(), 0o640)
        finally:
            os.close(directory)


def seal_tree(root, gid, expected=None):
    """Transfer ownership through no-follow descriptors before inspecting build output."""
    visited = 0
    def walk(directory, relative=""):
        nonlocal visited
        os.fchown(directory, 0, gid)
        os.fchmod(directory, 0o750)
        for entry in os.scandir(directory):
            visited += 1
            require(visited <= 100000, "too many build filesystem entries")
            name = entry.name
            local = posixpath.join(relative, name)
            info = os.stat(name, dir_fd=directory, follow_symlinks=False)
            if stat.S_ISLNK(info.st_mode):
                target = os.readlink(name, dir_fd=directory)
                category = local.split("/", 1)[0]
                resolved = posixpath.normpath(posixpath.join(posixpath.dirname(local), target))
                require(category in ("node_modules", "vendor") and not target.startswith("/")
                        and resolved.startswith(category + "/"), "unexpected build-created link: " + local)
                os.chown(name, 0, gid, dir_fd=directory, follow_symlinks=False)
                continue
            descriptor = os.open(name, os.O_RDONLY | os.O_NOFOLLOW | os.O_NONBLOCK,
                                 dir_fd=directory)
            try:
                actual = os.fstat(descriptor)
                require((info.st_ino, info.st_dev) == (actual.st_ino, actual.st_dev), "build path changed")
                if stat.S_ISDIR(actual.st_mode):
                    walk(descriptor, local)
                else:
                    require(stat.S_ISREG(actual.st_mode) and actual.st_nlink == 1,
                            "unexpected build output type or hard link: " + local)
                    if expected is not None:
                        require(local in expected or local in
                                ("source.tar", "public/build/manifest.json", "public/build/.vite/manifest.json")
                                or local.startswith(("node_modules/", "vendor/", ".build-home/", "public/build/assets/")),
                                "unexpected build-created source/artifact: " + local)
                    os.fchown(descriptor, 0, gid)
                    os.fchmod(descriptor, 0o640)
            finally:
                os.close(descriptor)
    descriptor = os.open(root, os.O_RDONLY | os.O_DIRECTORY | os.O_NOFOLLOW)
    try:
        walk(descriptor)
    finally:
        os.close(descriptor)


def generated_health(release, helper, gid):
    """Create only a new regular artifact in the sealed public directory."""
    private(helper)
    directory = os.open(release / "public", os.O_RDONLY | os.O_DIRECTORY | os.O_NOFOLLOW)
    try:
        info = os.fstat(directory)
        require(info.st_uid == 0 and not info.st_mode & 0o022, "public directory must be sealed")
        try:
            descriptor = os.open("_adsr-health.php", os.O_WRONLY | os.O_CREAT | os.O_EXCL | os.O_NOFOLLOW,
                                 0o640, dir_fd=directory)
        except FileExistsError:
            raise Stop("unexpected build-created health artifact")
        with os.fdopen(descriptor, "wb") as output, file_beneath(helper.parent, helper.name) as (content, info):
            require(info.st_size <= 65536, "oversized installed health helper")
            shutil.copyfileobj(content, output, length=HASH_CHUNK)
            output.flush()
            os.fsync(output.fileno())
            os.fchown(output.fileno(), 0, gid)
            os.fchmod(output.fileno(), 0o640)
    finally:
        os.close(directory)


def writable_cache(path):
    directory = os.open(path, os.O_RDONLY | os.O_DIRECTORY | os.O_NOFOLLOW)
    try:
        for name in os.listdir(directory):
            info = os.stat(name, dir_fd=directory, follow_symlinks=False)
            require(stat.S_ISDIR(info.st_mode) or stat.S_ISREG(info.st_mode), "unexpected runtime cache link/type")
            if stat.S_ISDIR(info.st_mode):
                writable_cache(path / name)
        # Grant directory writes only after traversal; no later root writes below it.
        os.fchmod(directory, 0o770)
    finally:
        os.close(directory)


def remove_archive_placeholders(path):
    require(not path.is_symlink(), "unexpected source storage symlink")
    if not path.exists():
        return
    require(path.is_dir(), "unexpected source storage file")
    entries = []
    for entry in path.rglob("*"):
        require(len(entries) < 4096, "too many archive storage placeholders")
        entries.append(entry)
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
        private(release)
        for relative in ("bootstrap/cache", "storage/framework/cache/data", "storage/framework/views"):
            writable_cache(release / relative)

    def no_build_processes(self):
        uid = pwd.getpwnam(self.build_user).pw_uid
        for process in Path("/proc").iterdir():
            if process.name.isdigit():
                try:
                    require(process.stat().st_uid != uid,
                            "build account still has a process; stop before root verification/writes")
                except FileNotFoundError:
                    pass

    def activation_gate(self):
        record = self.state / "host-rehearsal-approved.json"
        require(record.is_file(), "host Apache/mod_php/OPcache and rollback rehearsal approval required before daily activation")
        approval = read_json(record)
        require(all(approval.get(key) is True for key in
                    ("apache_modphp_switch", "opcache_release_identity", "write_preserving_rollback", "shared_paths"))
                and approval.get("controller_sha256") == fingerprint_file(self.lib, "deploy.py")["sha256"],
                "daily activation requires reviewed Apache/mod_php/OPcache and rollback rehearsal for installed controller")

    def prepare(self, candidate, previous, tree):
        private(self.root)
        private(self.releases)
        self.no_build_processes()
        schema_record = self.state / "schema-approved.json"
        require(schema_record.is_file(), "reviewed database schema fingerprint required")
        approved_schema = read_json(schema_record)["sha256"]
        require(isinstance(approved_schema, str) and re.fullmatch(r"[a-f0-9]{64}", approved_schema),
                "reviewed database schema fingerprint required")
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
        package = small_source_json(release, "package.json")
        composer = small_source_json(release, "composer.json")
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
        self.no_build_processes()
        seal_tree(release, pwd.getpwnam(self.web_user).pw_gid, expected)
        verify_source(release, expected)
        migration_gate(release, previous, self.shared / "database/preview.sqlite", approved_schema)
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
        generated_health(release, self.lib / "health.php", pwd.getpwnam(self.web_user).pw_gid)
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
        migration_gate(release, previous, self.shared / "database/preview.sqlite", approved_schema)
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
            if action == "daily":
                controller.activation_gate()
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
