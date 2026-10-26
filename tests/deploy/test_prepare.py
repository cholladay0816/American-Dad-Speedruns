"""Run Controller.prepare itself with real unprivileged synthetic build processes."""
import importlib.util
import json
import os
from pathlib import Path
import pwd
import resource
import shutil
import sqlite3
import subprocess
import tarfile
import tempfile
import unittest
from unittest.mock import patch

LIB = Path(__file__).resolve().parents[2] / 'ops/deploy'
spec = importlib.util.spec_from_file_location('prepare_deploy', LIB / 'deploy.py')
d = importlib.util.module_from_spec(spec)
spec.loader.exec_module(d)
A, TREE = 'a' * 40, 'c' * 40


class RealPrepare(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory(prefix='adsr-prepare-')
        self.root = Path(self.tmp.name)
        self.root.chmod(0o755)
        self.state = self.root / 'state'
        self.state.mkdir(mode=0o700)
        (self.root / 'releases').mkdir(mode=0o755)
        (self.root / 'releases').chmod(0o755)
        self.shared = self.root / 'shared'
        for name in ('database', 'storage/app/public', 'storage/logs', 'storage/framework/sessions', 'vite-assets'):
            (self.shared / name).mkdir(parents=True, mode=0o750)
        self.env = self.shared / '.env'
        self.env.write_text('protected existing environment; never replace')
        self.env.chmod(0o640)
        self.lib = self.root / 'lib'
        self.lib.mkdir(mode=0o755)
        for name in ('health.php', 'preflight.php'):
            shutil.copyfile(LIB / name, self.lib / name)
            (self.lib / name).chmod(0o644)
        self.installed = self.lib / 'deploy.py'
        self.installed.write_text('protected installed controller; never replace')
        self.installed.chmod(0o644)
        self.outside = self.root / 'build-owned-outside'
        self.outside.write_text('root=true\n')
        account = pwd.getpwnam('nobody')
        os.chown(self.outside, account.pw_uid, account.pw_gid)
        source = self.root / 'source'
        files = {
            '.editorconfig': 'root=true\n', 'public/index.php': '<?php // synthetic',
            'package.json': json.dumps({'scripts': {'build': 'vite build'}}),
            'composer.json': json.dumps({'scripts': {'post-autoload-dump': [
                'Illuminate\\Foundation\\ComposerScripts::postAutoloadDump', '@php artisan package:discover --ansi']}}),
            'database/migrations/2020_fixture.php': '<?php // unchanged synthetic migration',
            'storage/app/.gitignore': '*', 'storage/app/public/.gitignore': '*',
            'storage/logs/.gitignore': '*', 'storage/framework/sessions/.gitignore': '*',
            'storage/framework/cache/data/.gitignore': '*', 'storage/framework/views/.gitignore': '*',
            'bootstrap/cache/.gitignore': '*',
        }
        for name, content in files.items():
            target = source / name
            target.parent.mkdir(parents=True, exist_ok=True, mode=0o755)
            target.write_text(content)
            target.chmod(0o644)
        self.previous = self.root / 'releases/previous'
        shutil.copytree(source, self.previous)
        self.archive = self.root / 'source.tar'
        with tarfile.open(self.archive, 'w') as archive:
            for path in sorted(source.rglob('*')):
                archive.add(path, arcname=str(path.relative_to(source)), recursive=False)
        self.archive.chmod(0o644)
        self.db = self.shared / 'database/preview.sqlite'
        with sqlite3.connect(self.db) as connection:
            connection.executescript("CREATE TABLE migrations(migration TEXT); INSERT INTO migrations VALUES('2020_fixture');")
        d.atomic_json(self.state / 'schema-approved.json', {'sha256': d.schema_fingerprint(self.db)})
        self.controller = d.Controller({'root': str(self.root), 'build_user': 'nobody', 'mode': 'preview'},
                                       self.state, self.lib)
        self.attack = 'none'
        self.attack_ran = False
        self.protected = {self.env: self.env.read_bytes(), self.installed: self.installed.read_bytes()}
        self.controller.git = self.archive_command
        self.controller.step = self.external_step

    def tearDown(self):
        self.tmp.cleanup()

    def process(self, argv, cwd=None, user='nobody'):
        account = pwd.getpwnam(user)
        def unprivileged():
            os.setgroups([])
            os.setgid(account.pw_gid)
            os.setuid(account.pw_uid)
            resource.setrlimit(resource.RLIMIT_AS, (224_000_000, 224_000_000))
            resource.setrlimit(resource.RLIMIT_FSIZE, (2_147_483_648, 2_147_483_648))
        result = subprocess.run([str(arg) for arg in argv], cwd=cwd, preexec_fn=unprivileged,
                                capture_output=True, timeout=10)
        self.assertEqual(result.returncode, 0,
                         'synthetic process failed: ' + result.stderr.decode(errors='replace')[:2000])
        return result.stdout

    def archive_command(self, *args, **kwargs):
        destination = next(value.removeprefix('--output=') for value in args if value.startswith('--output='))
        self.process(['/usr/bin/cp', self.archive, destination])

    def external_step(self, name, argv, **kwargs):
        release = kwargs.get('cwd')
        if name == 'extract':
            self.process(argv, release)
        elif name == 'node-version':
            return 'v26.3.0'
        elif name == 'composer-version':
            return 'Composer version 2.8.12 synthetic fixture'
        elif name == 'npm-build':
            self.process(['/usr/bin/python3', '-c',
                "from pathlib import Path; p=Path('public/build/assets'); p.mkdir(parents=True); (p/'app-fixture.js').write_text('synthetic asset'); "
                "p=Path('node_modules/.bin'); p.mkdir(parents=True); q=Path('node_modules/vite/bin'); q.mkdir(parents=True); "
                "(q/'vite.js').write_text('synthetic'); (p/'vite').symlink_to('../vite/bin/vite.js')"], release)
        elif name == 'platform':
            if self.attack.startswith('health:'):
                target = self.env if self.attack.endswith('env') else self.installed
                self.process(['/usr/bin/python3', '-c',
                    "import pathlib,sys; pathlib.Path('public/_adsr-health.php').symlink_to(sys.argv[1])", target], release)
            elif self.attack == 'health-regular':
                self.process(['/usr/bin/python3', '-c',
                    "from pathlib import Path; Path('public/_adsr-health.php').write_text('untrusted artifact')"], release)
            elif self.attack == 'oversize':
                # Sparse 1 GiB file: reproduces the size attack without allocating it in RAM/disk.
                self.process(['/usr/bin/python3', '-c',
                    "with open('.editorconfig','wb') as f: f.truncate(1073741824)"], release)
            elif self.attack == 'public-directory-link':
                self.process(['/usr/bin/python3', '-c',
                    "import pathlib,sys; p=pathlib.Path('public'); p.rename('old-public'); p.symlink_to(sys.argv[1])", self.shared], release)
            elif self.attack == 'extra-php':
                self.process(['/usr/bin/python3', '-c',
                    "from pathlib import Path; Path('public/injected.php').write_text('<?php // unreviewed')"], release)
            elif self.attack == 'hardlink':
                self.process(['/usr/bin/python3', '-c',
                    "import os,pathlib,sys; pathlib.Path('.editorconfig').unlink(); os.link(sys.argv[1], '.editorconfig')",
                    self.outside], release)
            self.attack_ran = self.attack != 'none'
        elif name == 'effective-app-config':
            return json.dumps({'key_present': True, 'debug': False, 'driver': 'sqlite',
                'database': str(self.db), 'mailer': 'array', 'cookie': 'adsr_php85_preview_session', 'session': 'file'})
        return None

    def unchanged(self):
        if self.attack != 'none':
            self.assertTrue(self.attack_ran, 'the injection must run before a security refusal can pass')
        for path, content in self.protected.items():
            self.assertEqual(path.read_bytes(), content)
            self.assertEqual(path.stat().st_uid, 0)

    def test_actual_prepare_success_seals_source_and_creates_regular_generated_health(self):
        prepared = self.controller.prepare(A, self.previous, TREE)
        health = prepared / 'public/_adsr-health.php'
        self.assertFalse(health.is_symlink())
        self.assertEqual(health.read_bytes(), (self.lib / 'health.php').read_bytes())
        self.assertEqual((prepared / '.editorconfig').stat().st_uid, 0)
        self.assertEqual((prepared / '.editorconfig').stat().st_mode & 0o022, 0)
        self.assertTrue((prepared / 'node_modules/.bin/vite').is_symlink())
        self.assertTrue((prepared / 'storage/app').is_symlink())
        identity = prepared / '.adsr-release.json'
        readback = self.process(['/usr/bin/python3', '-c',
            'import pathlib,sys; print(pathlib.Path(sys.argv[1]).read_text())', identity],
            cwd=prepared, user=self.controller.web_user)
        self.assertEqual(json.loads(readback), {'commit': A, 'tree': TREE})
        self.assertEqual(identity.stat().st_uid, 0)
        self.assertEqual(identity.stat().st_gid, pwd.getpwnam(self.controller.web_user).pw_gid)
        self.assertEqual(identity.stat().st_mode & 0o777, 0o640)
        self.assertEqual((self.state / 'schema-approved.json').stat().st_mode & 0o777, 0o600)
        d.installed_source(prepared, A)
        self.unchanged()

    def test_actual_prepare_rejects_health_symlink_to_shared_environment(self):
        self.attack = 'health:env'
        with self.assertRaises(d.Stop):
            self.controller.prepare(A, self.previous, TREE)
        self.unchanged()

    def test_actual_prepare_rejects_health_symlink_to_installed_controller(self):
        self.attack = 'health:controller'
        with self.assertRaises(d.Stop):
            self.controller.prepare(A, self.previous, TREE)
        self.unchanged()

    def test_actual_prepare_rejects_public_parent_symlink(self):
        self.attack = 'public-directory-link'
        with self.assertRaises(d.Stop):
            self.controller.prepare(A, self.previous, TREE)
        self.unchanged()

    def test_actual_prepare_refuses_existing_regular_generated_artifact(self):
        self.attack = 'health-regular'
        with self.assertRaises(d.Stop):
            self.controller.prepare(A, self.previous, TREE)
        self.unchanged()

    def test_actual_prepare_rejects_untracked_public_php_injection(self):
        self.attack = 'extra-php'
        with self.assertRaisesRegex(d.Stop, 'unexpected build-created'):
            self.controller.prepare(A, self.previous, TREE)
        self.unchanged()

    def test_actual_prepare_rejects_gigabyte_tracked_output_before_hash_read(self):
        self.attack = 'oversize'
        original = d.hash_stream
        def bounded(stream, size):
            self.assertLessEqual(size, d.MAX_SOURCE_FILE, 'oversized file reached root hashing')
            return original(stream, size)
        before = resource.getrusage(resource.RUSAGE_SELF).ru_maxrss
        with patch.object(d, 'hash_stream', side_effect=bounded), self.assertRaisesRegex(d.Stop, 'source size differs'):
            self.controller.prepare(A, self.previous, TREE)
        self.assertLess(resource.getrusage(resource.RUSAGE_SELF).ru_maxrss - before, 16 * 1024)
        self.unchanged()

    def test_actual_prepare_rejects_hardlink_without_changing_outside_owner(self):
        self.attack = 'hardlink'
        owner = self.outside.stat().st_uid
        with self.assertRaisesRegex(d.Stop, 'hard link'):
            self.controller.prepare(A, self.previous, TREE)
        self.assertEqual(self.outside.stat().st_uid, owner)
        self.assertEqual(self.outside.read_text(), 'root=true\n')
        self.unchanged()


if __name__ == '__main__':
    unittest.main()
