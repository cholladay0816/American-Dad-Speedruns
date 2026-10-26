#!/usr/bin/python3
"""Prepared production minute runner. Never enable on the direct-IP preview."""
import json
import datetime as dt
import os
from pathlib import Path
import sys

import deploy as d


def record_missed_midnight(state, now, busy=False):
    day = now.date().isoformat()
    record = state / 'last-midnight.json'
    attempted = d.read_json(record).get('day') if record.exists() else None
    if attempted != day and (busy or (now.hour, now.minute) != (0, 0)):
        d.atomic_json(state / 'missed-midnight.json', {'day': day, 'status': 'not-observed',
                      'reason': 'shared lock busy' if busy else 'midnight invocation not recorded'})


def run_jobs(controller):
    d.require(controller.c['mode'] == 'production', 'application jobs disabled in preview')
    controller.validate_mode()
    _, release = controller.validate_current()
    app = json.loads(controller.step('jobs-preflight', ['/usr/bin/php8.5', controller.lib / 'preflight.php'],
                                     user='www-data', cwd=release, capture=True))
    controller.validate_app(app)
    d.require(app.get('queue') == 'database' and app.get('failed_driver') == 'database-uuids'
              and app.get('retry_after') == 90
              and app.get('timezone') == 'UTC' and app.get('pcntl') is True,
              'production jobs need reviewed database queue, retry_after=90, UTC schedule and pcntl')
    now = dt.datetime.now(dt.timezone.utc)
    if (now.hour, now.minute) == (0, 0):
        d.atomic_json(controller.state / 'last-midnight.json', {'day': now.date().isoformat(), 'status': 'attempted'})
    controller.step('schedule-run', ['/usr/bin/php8.5', '-d', 'memory_limit=192M', 'artisan',
                    'schedule:run', '--no-interaction'], user='www-data', cwd=release)
    controller.step('drain-queue', ['/usr/bin/php8.5', '-d', 'memory_limit=192M', 'artisan',
                    'queue:work', 'database', '--queue=default', '--stop-when-empty', '--max-time=45',
                    '--timeout=60', '--tries=1', '--sleep=1', '--memory=128', '--no-interaction'],
                    user='www-data', cwd=release)


def main():
    os.umask(0o077)
    d.require(os.geteuid() == 0, 'reviewed root installation required')
    config = d.read_json(d.CONFIG)
    # Preview invocation is a cheap no-op; it never boots the application or touches a queue.
    if config.get('mode') != 'production':
        return 0
    controller = d.Controller(config)
    with d.exclusive(d.STATE) as acquired:
        record_missed_midnight(d.STATE, dt.datetime.now(dt.timezone.utc), busy=not acquired)
        if not acquired or any((d.STATE / name).exists() for name in
                ('failure.json', 'job-failure.json', 'transaction.json')):
            return 0
        try:
            run_jobs(controller)
            d.atomic_json(d.STATE / 'last-jobs.json', {'status': 'completed', 'at': controller.run_id})
            return 0
        except Exception as error:
            reason = str(error) if isinstance(error, d.Stop) else 'application jobs failed; inspect private run.log'
            d.atomic_json(d.STATE / 'job-failure.json', {'status': 'failed', 'at': controller.run_id, 'reason': reason})
            return 1


if __name__ == '__main__':
    try:
        sys.exit(main())
    except Exception:
        sys.exit(1)
