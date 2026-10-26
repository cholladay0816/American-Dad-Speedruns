#!/usr/bin/python3
"""Check effective cgroup v2 limits before exec; used only by the installed controller."""
import os
from pathlib import Path
import subprocess
import sys


def verify(unit):
    relative = next(line.split(":", 2)[2] for line in Path("/proc/self/cgroup").read_text().splitlines()
                    if line.startswith("0::"))
    group = Path("/sys/fs/cgroup") / relative.lstrip("/")
    assert 0 < int((group / "memory.max").read_text()) <= 384000000
    assert int((group / "memory.swap.max").read_text()) == 0
    assert 0 < int((group / "pids.max").read_text()) <= 128
    quota, period = (group / "cpu.max").read_text().split()
    assert 0 < int(quota) <= int(period)
    result = subprocess.run(["/usr/bin/systemctl", "show", unit,
        "--property=RuntimeMaxUSec,TimeoutStopUSec,KillMode,OOMPolicy,NoNewPrivileges"],
        text=True, capture_output=True, timeout=5, check=True)
    properties = dict(line.split("=", 1) for line in result.stdout.splitlines())
    assert properties == {"RuntimeMaxUSec": "10min", "TimeoutStopUSec": "5s",
                          "KillMode": "control-group", "OOMPolicy": "kill", "NoNewPrivileges": "yes"}


def main():
    unit, *command = sys.argv[1:]
    assert unit.startswith("adsr-deploy-") and unit.endswith(".service")
    if os.geteuid() == 0:
        assert command in (["/usr/sbin/apache2ctl", "configtest"],
                           ["/usr/bin/systemctl", "reload", "apache2"])
    verify(unit)
    os.execv(command[0], command)


if __name__ == "__main__":
    try:
        main()
    except Exception:
        print("Effective resource constraints could not be verified; command was not started.", file=sys.stderr)
        sys.exit(1)
