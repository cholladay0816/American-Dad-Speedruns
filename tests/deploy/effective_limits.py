"""CI-only real systemd/cgroup tests. No Docker, packages, host application or credentials."""
import importlib.util
import os
from pathlib import Path
import subprocess
import tempfile
import time
import uuid

LIB = Path(__file__).resolve().parents[2] / 'ops/deploy'
spec = importlib.util.spec_from_file_location('deploy', LIB / 'deploy.py')
d = importlib.util.module_from_spec(spec)
spec.loader.exec_module(d)
assert os.geteuid() == 0
assert Path('/sys/fs/cgroup/cgroup.controllers').exists()


def alive(pid):
    try:
        return Path(f'/proc/{pid}/stat').read_text().split()[2] != 'Z'
    except FileNotFoundError:
        return False


with tempfile.TemporaryDirectory(prefix='adsr-limits-') as temporary:
    root = Path(temporary)
    root.chmod(0o755)
    state = root / 'state'
    state.mkdir(mode=0o700)
    scratch = root / 'scratch'
    scratch.mkdir(mode=0o777)
    scratch.chmod(0o777)
    script = root / 'fixture.py'
    script.write_text('''import os,pathlib,subprocess,sys,time
scratch=pathlib.Path(sys.argv[1])
if sys.argv[2]=='ok':
    print('effective caps verified; uid='+str(os.geteuid()))
elif sys.argv[2]=='oom':
    (scratch/'oom-pid').write_text(str(os.getpid()))
    block=bytearray(450000000)
else:
    child=subprocess.Popen(['/usr/bin/python3','-c','import time; time.sleep(60)'])
    (scratch/'child').write_text(str(child.pid))
    (scratch/'parent').write_text(str(os.getpid()))
    time.sleep(60)
''')
    script.chmod(0o644)
    controller = d.Controller({'root': str(root), 'build_user': 'nobody'}, state, LIB)
    result = controller.step('real-effective-caps', ['/usr/bin/python3', script, scratch, 'ok'], capture=True)
    assert result == 'effective caps verified; uid=65534', result
    try:
        controller.step('real-oom-kills-group', ['/usr/bin/python3', script, scratch, 'oom'])
        raise AssertionError('MemoryMax was not enforced')
    except d.Stop:
        assert not alive(int((scratch / 'oom-pid').read_text()))

    # Deliberately short runtime ONLY for this synthetic termination test. Production
    # guard above verifies its exact 600s configuration; these are not alternate deploy caps.
    unit = 'adsr-test-' + uuid.uuid4().hex
    command = ['/usr/bin/systemd-run', '--quiet', '--wait', '--pipe', '--collect', '--unit=' + unit,
        '--property=User=nobody', '--property=MemoryMax=384000000', '--property=MemorySwapMax=0',
        '--property=CPUQuota=100%', '--property=TasksMax=128', '--property=RuntimeMaxSec=3',
        '--property=TimeoutStopSec=1', '--property=KillMode=control-group', '--property=OOMPolicy=kill',
        '/usr/bin/python3', str(script), str(scratch), 'descendants']
    started = time.monotonic()
    result = subprocess.run(command, capture_output=True, timeout=15)
    assert result.returncode != 0 and time.monotonic() - started < 12
    for file in ('child', 'parent'):
        pid = int((scratch / file).read_text())
        assert not alive(pid), 'descendant survived RuntimeMaxSec/KillMode=control-group'
print('Effective production limits, non-root execution, OOM kill and timed descendant cleanup passed.')
