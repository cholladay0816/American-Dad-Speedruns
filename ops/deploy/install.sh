#!/bin/sh
# Operator runs a reviewed, immutable copy. Does not install packages/accounts or edit Apache.
set -eu
[ "$(id -u)" = 0 ] || { echo 'Run the reviewed installer as root.' >&2; exit 1; }
[ "$#" = 1 ] && [ "$1" = '--install-disabled' ] || {
    echo 'Only --install-disabled is supported. Cron enablement is a separate reviewed step.' >&2; exit 1;
}
cd "$(dirname "$0")"
for binary in /usr/bin/python3 /usr/bin/git /usr/bin/php8.5 /usr/local/bin/node /usr/local/bin/npm /usr/local/bin/composer /usr/bin/systemd-run /usr/bin/curl /usr/sbin/apache2ctl; do
    [ -x "$binary" ] || { echo "Required verified binary missing: $binary" >&2; exit 1; }
done
id adsr-build >/dev/null 2>&1 || { echo 'Required isolated adsr-build account missing; see README for explicit privilege review.' >&2; exit 1; }
[ -f /sys/fs/cgroup/cgroup.controllers ] || { echo 'Effective cgroup v2 limits required.' >&2; exit 1; }
[ -L /srv/adsr-preview/current ] || { echo 'Bootstrap reviewed current symlink and Apache first.' >&2; exit 1; }
[ -f /etc/adsr-deploy/config.json ] && [ -f /etc/adsr-deploy/preview-ca.pem ] && [ -f /var/lib/adsr-deploy/active.json ] || {
    echo 'Prepare root-owned reviewed config, public CA certificate and baseline active metadata first.' >&2; exit 1;
}
if [ -e /etc/cron.d/adsr-deploy ]; then
    echo 'Existing deployment cron present; pause and review before replacing installed controller.' >&2; exit 1;
fi
/usr/bin/python3 - <<'PY'
import json, os, pathlib, pwd, subprocess
import importlib.util
spec = importlib.util.spec_from_file_location('deploy', pathlib.Path('deploy.py'))
controller = importlib.util.module_from_spec(spec)
spec.loader.exec_module(controller)
for value in ['/etc/adsr-deploy', '/etc/adsr-deploy/config.json', '/etc/adsr-deploy/preview-ca.pem',
              '/var/lib/adsr-deploy', '/var/lib/adsr-deploy/active.json']:
    path = pathlib.Path(value)
    info = path.lstat()
    assert not path.is_symlink() and info.st_uid == 0 and not info.st_mode & 0o022
config = controller.read_json(pathlib.Path('/etc/adsr-deploy/config.json'))
assert config['root'] == '/srv/adsr-preview' and config['build_user'] == 'adsr-build' and config['mode'] == 'preview'
assert set(config['workflows']) == {'.github/workflows/laravel.yml', '.github/workflows/guarded-deploy.yml'}
assert all(type(w['id']) is int and w['id'] > 0 for w in config['workflows'].values())
assert config['tls_ca'] == '/etc/adsr-deploy/preview-ca.pem'
account = pwd.getpwnam('adsr-build')
assert account.pw_uid != 0 and account.pw_shell in ('/usr/sbin/nologin', '/bin/false')
groups = os.getgrouplist('adsr-build', account.pw_gid)
assert groups == [account.pw_gid]
assert not any(pwd.getpwnam(name).pw_gid in groups for name in ['root', 'www-data'])
assert subprocess.check_output(['/usr/local/bin/node', '--version'], text=True).strip() == 'v26.3.0'
assert subprocess.check_output(['/usr/bin/php8.5', '-r', 'echo PHP_VERSION;'], text=True) == '8.5.12'
active = controller.read_json(pathlib.Path('/var/lib/adsr-deploy/active.json'))
assert not active.get('overlay'), 'Unmerged preview overlay must be reconciled before installation'
release = pathlib.Path(active['release'])
assert release.parent == pathlib.Path('/srv/adsr-preview/releases') and not release.is_symlink()
assert pathlib.Path('/srv/adsr-preview/current').resolve() == release
assert controller.read_json(release / '.adsr-release.json')['commit'] == active['commit']
assert not controller.read_json(release / '.adsr-release.json').get('overlay')
controller.installed_source(release, active['commit'])
gate = pathlib.Path('/var/lib/adsr-deploy/preview-required.json')
assert gate.is_file() and controller.read_json(gate) == {'required': True}
assert gate.stat().st_uid == 0 and not gate.stat().st_mode & 0o077 and not gate.is_symlink()
assert controller.fingerprint_file(release, 'public/_adsr-health.php') == controller.fingerprint_file(pathlib.Path.cwd(), 'health.php')
assert pathlib.Path('/srv/adsr-preview/shared/database/preview.sqlite').is_file()
assert pathlib.Path('/srv/adsr-preview/shared/.env').is_file()
assets = pathlib.Path('/srv/adsr-preview/shared/vite-assets')
assert assets.is_dir() and not assets.is_symlink() and assets.stat().st_uid == 0 and not assets.stat().st_mode & 0o022
for asset in (release / 'public/build/assets').iterdir():
    target = assets / asset.name
    assert asset.is_file() and not asset.is_symlink() and target.is_file() and not target.is_symlink()
    assert controller.fingerprint_file(asset.parent, asset.name) == controller.fingerprint_file(assets, asset.name)
schema = controller.read_json(pathlib.Path('/var/lib/adsr-deploy/schema-approved.json'))['sha256']
assert schema == controller.schema_fingerprint(pathlib.Path('/srv/adsr-preview/shared/database/preview.sqlite'))
PY
install -d -o root -g root -m 0755 /usr/local/lib/adsr-deploy
install -o root -g root -m 0644 deploy.py caps_guard.py jobs.py make_manifest.py preflight.php health.php /usr/local/lib/adsr-deploy/
install -d -o root -g root -m 0700 /var/lib/adsr-deploy/approvals
install -o root -g root -m 0644 adsr-deploy.logrotate /etc/logrotate.d/adsr-deploy
echo 'Controller installed DISABLED. No cron, Apache reload, source deployment or application jobs were started.'
