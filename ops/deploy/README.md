# Guarded daily source deployments — NEW preview instance only

Prepared configuration; **nothing in this PR installs cron or changes a host**.
The parent operator reviews this PR, then uses the existing authorized browser SSH
on the new Debian 12 / Apache mod_php 8.5.12 instance. The old live server and static
IP remain unchanged until the owner accepts the direct-IP preview. This replaces
neither that acceptance nor the later cutover approval. No certificate renewal is
included; the preview IP certificate expires **2026-11-01 20:24:22 UTC**.

The controller lives outside releases, owned by root. Fetched code cannot replace
it. Dependencies and source commands run without root under effective systemd v2
limits: `MemoryMax=384000000`, `MemorySwapMax=0`, `CPUQuota=100%`, `TasksMax=128`,
`RuntimeMaxSec=600`, `TimeoutStopSec=5`, `KillMode=control-group`, `OOMPolicy=kill`.
The installed guard checks actual cgroup files and service properties before exec.
Unavailable controllers, lower capacity, command failure or unverifiable limits
stop the operation; do not increase the caps. Fixed Apache control commands alone
run as root. No IAM/key/firewall/account privilege changes are performed.

## Layout and bootstrap review

| Path | Purpose |
| --- | --- |
| `/srv/adsr-preview/current` | Atomic symlink; Apache serves **only** `current/public` |
| `/srv/adsr-preview/releases/<sha>-<attempt>` | Separate source, locked vendors/assets and per-release compiled views/cache |
| `/srv/adsr-preview/source.git` | Bare public Git source cache; owned only by the isolated build account |
| `/srv/adsr-preview/shared/.env` | Existing application key/settings; never print, generate or replace |
| `/srv/adsr-preview/shared/database/preview.sqlite` | Existing working DB; absolute effective configuration required |
| `/srv/adsr-preview/shared/storage/app` | Persistent uploads, including `public/` |
| `/srv/adsr-preview/shared/storage/logs` | Existing application logs, never exported by this controller |
| `/srv/adsr-preview/shared/storage/framework/sessions` | Persistent preview sessions |
| `/srv/adsr-preview/shared/vite-assets` | Root-owned append-only generated assets, served by Apache Alias across switch/rollback |
| `/etc/adsr-deploy` | Root-owned reviewed config and public TLS trust certificate |
| `/var/lib/adsr-deploy` | Root `0700`; locks, approvals, transaction journal, failure latch, results and `0600` run log |

The currently extracted archive may have no Git checkout. Do not `git init`, pull,
reset, clean or build inside it. Preserve `/srv/adsr-preview/releases/e97f98c674f5`,
the working DB, all user uploads and the protected snapshot/backup at
`/home/admin/.adsr-preview-20261026T0436Z`. No release or backup retention deletion
is authorized. Generated dependency trees and source archives are also retained;
when disk is low deployment stops for an operator retention decision.

Inventory verified by the parent at 2026-10-26 06:12 UTC: systemd 252.39 with
cgroup v2 CPU/memory/pids, about 35 GB disk free, 945 MiB RAM total and about
472 MiB available, no swap. `git` and `cron` are **absent**. Node/npm currently
live under `/home/admin/.adsr-build-e97f98c674f5/tools/node-v26.3.0-linux-x64/bin`
and Composer is `tools/composer.phar` in that private admin tree. The installer
requires durable verified `/usr/local/bin` entry points: the parent must review
copying the already verified tools to root-owned `/opt/adsr-tools` and narrowly
linking those binaries. Do not broaden the private admin tree's permissions or
silently download replacements. Package installation (`git`, `cron`, and missing
prerequisites) is a separate operator change. Before starting newly installed cron,
inspect existing `/etc/cron.d/e2scrub_all` and `/etc/cron.d/php` effects; do not alter
unrelated entries. Root/admin crontabs and `/etc/crontab` were absent.

At inventory time `.env`, app storage and compiled caches were **inside the
existing release**, owned `admin:www-data`; working DB was already shared. The
parent must explicitly preserve/rehome these persistent paths and retarget
`public/storage`. Current `SESSION_COOKIE=adsr_php85_preview_session` is retained.
`APP_ENV=production` is a framework setting and provides no cutover authorization.
The installer requires root `0600` `preview-required.json` with `{"required":true}`;
it must persist through all preview deployments. Preserve the vhost's
`X-ADSR-Preview: snapshot; final-resync-required` and `X-Robots-Tag: noindex, nofollow`
headers. No application schedule/worker or certificate timer was installed.

PR #73's homepage title filter was rejected and closed unmerged. The parent is
restoring the original `e97f98c` controller and applying the owner's targeted banner
expiration update to SQLite instead. **Do not merge, preserve or resurrect that
filter.** The original source fingerprint must match the restored `e97f98c` release;
the approved banner row expiration is persistent application data, outside source
fingerprints and unrelated to the unchanged migration ledger.

Generic overlay protection remains: record any unreconciled source overlay as an
`overlay` object in private active metadata and the generated release manifest.
Installation/activation and every deployment refuse a nonempty overlay. After the
parent restores source and verifies its exact original fingerprint, it may remove
the obsolete overlay metadata and retain the true `e97f98c` baseline identity.
This requires no new PR merge or fresh source release. Do not falsely label a
modified tree as plain `e97f98c`, silently discard an unreviewed overlay, fetch an
untrusted deployment branch, or merge this deployment PR automatically.
Additionally, a root-owned `.adsr-source.json` fingerprint manifest must match the
actual baseline files, so omitting overlay metadata cannot bypass reconciliation.
Generate it from an **independently verified original Git archive**, never from the
overlaid serving directory: on the trusted source checkout use
`git archive --format=tar --output=baseline.tar <full-commit>`, then on a Linux
Python runtime run `python3 ops/deploy/make_manifest.py baseline.tar <full-commit>
baseline-source.json`. Verify archive provenance/hash before copying that generated
manifest to the baseline as `.adsr-source.json`, root:`www-data` `0640`. The helper
does not fetch, execute or extract source. A plain archive label is not verification;
the source archive must come from the trusted immutable Git object. Persistent
`storage/` placeholders are excluded from installed-file checks; env, uploaded data
and generated artifacts are never used to manufacture source provenance.

Before any installer mutation, the operator must verify these prerequisites:

1. A protected recovery point and tested restoration exist; resolve actual shared
   upload/session/env locations without exposing their values. The table specifies
   the required layout; it is **not** a claim that all links already exist. Move or
   copy existing data only through the parent's reviewed preservation plan. Never
   recreate the DB, generate `APP_KEY`, run migrations, restore a DB over new writes,
   or remove old data. Existing SQLite ledger has 31 applied migrations.
2. Confirm Apache uses mod_php 8.5.12, its actual vhost and document root, hidden-file
   denial, the current PHP extensions, and the public IP certificate's verified
   chain. FPM is a blocking architecture change: this implementation reloads Apache
   for mod_php OPcache/realpath caches and must be adapted/reviewed for FPM first.
3. A dedicated `adsr-build` system account is required, with nologin shell, no sudo,
   no supplementary groups, no production data access and no SSH keys. **The
   installer refuses to create it.** If absent, surface that local account/ownership
   expansion for review; an operator may explicitly provision it with
   `adduser --system --group --no-create-home --home /nonexistent adsr-build`.
   Do not reuse `admin`, root or `www-data` for npm/Composer installation.
4. Root owns the deployment root/release parent (`0755`) so the build and web users
   can traverse but cannot change the `current` link. Root controls shared parents;
   only approved app directories and the existing SQLite file are writable by
   `www-data`. Build user cannot access `.env`, DB, uploads, sessions or logs.
   Root creates a separate `source.git` with `git init --bare`, then assigns only
   that cache to `adsr-build`. No Git credentials are installed; fetch uses the
   fixed public HTTPS remote with terminal prompts/helpers disabled.
5. Verify installed Node **26.3.0**, Composer **2.8.12**, PHP **8.5.12**, `cron`,
   `curl`, `git`, `tar`, `logrotate`, Python 3.11 with `zoneinfo`/Chicago tzdata and
   effective cgroup v2 controls. Do not download/install a replacement implicitly.
   Every operation requires >=480,000,000 available RAM bytes and >=2,000,000,000
   free disk bytes. HTTP recovery and fixed Apache control still attempt bounded
   rollback if capacity falls after switching; builds never bypass reserve checks.

## Install disabled, then rehearse and enable deployment cron

Use a verified immutable archive of the reviewed PR head; do not execute an
installer from mutable fetched master. These steps describe parent/operator work
after review; none have been executed by this coding task.

1. Create `/etc/adsr-deploy` root `0755` and `/var/lib/adsr-deploy` root `0700`.
   Create root `0600` `preview-required.json` containing `{"required":true}`.
   Copy `config.example.json` to `config.json` root `0600`, retain `mode=preview`,
   and reverify the included workflow IDs against the repository's Actions records:
   `5698625` for `laravel.yml`, `396712994` for `guarded-deploy.yml` (read via the
   authenticated owner API on 2026-10-26). Require exactly `laravel.yml` → `regression` and
   `guarded-deploy.yml` → `deployment-guards`, `effective-limits`. A deleted/recreated
   workflow requires fresh review. The public trust certificate at `preview-ca.pem` must be readable
   by `www-data` (`0644`), must validate the existing certificate for `3.89.97.195`,
   and must not contain any private key. No insecure TLS option or renewal exists.
2. Bootstrap `current` to the existing reviewed `e97f98c674f5` release, then point
   only the new Apache SSL vhost at `current/public`, retaining its PHP handler.
   Include `apache-health.conf`'s local restriction and immutable asset Alias. Seed
   root:`www-data` `0750` `shared/vite-assets` with the verified baseline's generated
   `public/build/assets` files, preserving names/bytes, root:`www-data` `0640`.
   The installer verifies this seed; it never rewrites or deletes asset versions.
   Existing HTTP redirects and TLS
   stay in the parent-reviewed vhost. Do not change the old server or static IP.
3. The baseline needs the same generated identity/health metadata as future
   releases. After verifying its archive provenance, copy this reviewed `health.php`
   to `public/_adsr-health.php` root:`www-data` `0640` in that baseline only; write
   root:`www-data` `0640` `.adsr-release.json` containing
   `{"commit":"e97f98c674f5148b63be99c4acc09a855b1e434c"}`. The health file is a
   generated deployment artifact, outside the upstream source tree fingerprint.
   Create root `0600` `/var/lib/adsr-deploy/active.json` with
   `{"commit":"e97f98c674f5148b63be99c4acc09a855b1e434c","release":"/srv/adsr-preview/releases/e97f98c674f5"}`.
   No DB or app code changes are needed. Test the vhost configuration and perform
   the reviewed graceful Apache reload. Verify the loopback endpoint returns that
   exact baseline, and remote access is denied. Also verify a safe `/login` page
   through HTTPS. Do not rely on a successful CLI boot to prove the serving PHP.
4. Preview `.env` must retain its existing key, SQLite absolute path, `APP_DEBUG=false`,
   `MAIL_MAILER=array`, isolated `SESSION_COOKIE=adsr_php85_preview_session` and persistent
   file/database sessions. Match the existing verified isolated preview cookie; if
   its current name differs, adapt this explicitly reviewed constant before install
   instead of silently resetting user sessions. Do not boot scheduler or workers.
5. Run `sh ops/deploy/install.sh --install-disabled` from the immutable reviewed
   package. It checks prerequisites, then installs root-owned helpers and local log
   rotation only. It does **not** edit Apache, initialize/move application data,
   create accounts, deploy source, enable cron or start services. Ensure source cache
   ownership and deployment parents match step 4 of the bootstrap review.
6. Run the installed controller's `run` manually and inspect root-private
   `last-result.json`/`run.log`. With unchanged master, expect `unchanged`, one bounded
   public ref lookup, no API/dependencies/reload. Rehearse an approved source update
   and forced health failure on synthetic staging with shared writes preserved;
   CI provides synthetic evidence, not a claim of a host rehearsal. Verify memory
   limits, capacity, local HTTP identity, permissions and rollback on the actual host.
7. After that review, explicitly enable only deployment cron:

   ```sh
   install -o root -g root -m 0644 ops/deploy/adsr-deploy.cron /etc/cron.d/adsr-deploy
   ```

   Verify the installed file has a final newline, `cron` is active, and read
   `daily.json`/`last-result.json` after the next eligible run. This is actual Debian
   cron, not a systemd timer. It wakes each minute; the locked controller permits
   **one attempt per America/Chicago date after 03:30**, with catch-up after downtime.
   The host timezone is never changed. Spring-forward 2026-03-08 runs at 08:30 UTC;
   fall-back 2026-11-01 at 09:30 UTC. Neither repeats or skips that local 03:30 slot.
   Failed attempts count for the date and latch off further unattended runs.
   Clock/date rollback is not a supported scheduling operation; review state before
   a manual clock change. A busy lock does not consume the day's attempt.

## Source and CI approval contract

Only fast-forward evolution of the recorded active commit to current public
`master` is eligible. Fetching does not trust the mutable branch for execution:
the controller verifies Git objects, immutable commit, tree, ancestry and exact
approved PR head. Builds use `git archive <full-sha>` into a new directory. Source
fingerprints must match after npm/Composer installation. Symlinks/submodules in
source, new/revised migration files or ledger differences stop deployment.

A root operator records review of **each** deployment candidate after PR review
and exact-head CI. This intentionally requires a new local approval record for a
new commit; daily polling cannot silently approve arbitrary merged code. Store
root `0600` `approvals/<master-merge-sha>.json` under the private state directory:

```json
{
  "pr": 74,
  "head": "<40-hex reviewed and CI-tested PR head>",
  "tree": "<40-hex tree shared by PR head and master merge>",
  "compatible_without_migrations": true
}
```

The example PR number is illustrative; use the actual reviewed PR. The approval
must cover concurrent old/new requests during Apache graceful drain, unchanged
schema, persistent sessions/uploads, cache formats, and write-preserving code
rollback. No approval means stop with an actionable failure latch. A master change
after review, rewrite, squash/rebase without head ancestry, merge conflict
resolution that changes the tree, fork head, unexpected merger, pending/missing/
failed/skipped CI, API error/rate limit or pagination ambiguity all fail closed.
The candidate must be that PR's owner-merged commit, so multiple unapproved direct
changes cannot ride along. No new GitHub token is needed; public API rate limits
apply. Unchanged days do not call the API.

The gate uses the **latest attempt** of both pinned workflow IDs on the exact
approved head, checks successful required jobs and GitHub repository identities,
then checks the approved head is an ancestor and has the identical tree. A merge
SHA need not have its own checks. Both workflows explicitly check out the PR head
and persist no checkout credentials; existing application CI therefore tests that
head rather than GitHub's synthetic merge tree.

Build sequence is serial: `npm ci --include=dev --ignore-scripts`, then
`npm run build --ignore-scripts` **before vendor exists** (Tailwind scans vendor),
then Composer `install --no-dev --prefer-dist --optimize-autoloader --no-scripts
--no-plugins --no-interaction`, platform checks and source verification.
`NODE_OPTIONS=--max-old-space-size=192`; Composer memory `224M`, HTTP/process
parallelism 1. Only the exact reviewed `post-autoload-dump` list
(`ComposerScripts::postAutoloadDump`, `artisan package:discover`) runs explicitly
after effective config checks, under `www-data`, with plugins disabled. No install
hooks, key generation, dependency updates, migrations or root builds run.

Per-release views and application caches avoid stale compiled paths and shared
cache clearing. Persistent sessions, uploads, DB and environment are linked only
after builds. Built code/vendor/assets become root-owned read-only; only local
compiled cache/view directories and approved shared paths are web-writable.

## Atomic switch, failures and recovery

The shared host lock spans lookup, build, switch, checks and recovery; all prepared
application job invocations use it too. New source never runs inside the serving
tree. A startup check refuses leftover active deployment cgroups after a killed
controller, preserving one operation at a time even after its file lock is released.
Preflight checks effective configuration, platform and unchanged migration
ledger; previous HTTP health must pass before building. A durable transaction
journal precedes atomic symlink rename, Apache config check/graceful reload and
loopback HTTPS health. The endpoint boots Laravel, performs only `SELECT 1`, and
reports resolved release/commit without session middleware. Five bounded probes
require the new PHP/OPcache path; stale identities fail. There may be old requests
draining while new requests start, which is why explicit compatibility review is
required. This is not a write freeze or a schema migration mechanism.
New generated Vite assets are appended to the root-owned shared asset store before
switching; identical names must have identical bytes. The reviewed Apache Alias
serves that store, so pages opened on either release retain their hashed assets
through a switch or rollback. Assets are never automatically deleted or overwritten.
Unexpected asset types, links, nested layouts or hash collisions stop deployment.

Failure after switching restores the previous code link, reloads Apache and
checks its exact identity. **DB, uploads and sessions remain the same objects**;
accepted writes survive rollback. No DB rollback/restore/delete exists. A failed
rollback keeps the journal and latch. Process death keeps enough durable metadata
for manual recovery; deployment refuses to guess or start another release.

Operator commands (installed root controller):

```sh
python3 /usr/local/lib/adsr-deploy/deploy.py run
python3 /usr/local/lib/adsr-deploy/deploy.py recover
python3 /usr/local/lib/adsr-deploy/deploy.py clear-failure
```

Inspect `failure.json`, `transaction.json`, `active.json` and `last-result.json`
locally; do not paste logs/env/DB contents into chat or public GitHub. Resolve the
cause before `clear-failure`, which also checks current identity/HTTP health.
`recover` selects the durable active metadata (previous release if not committed,
new release if already committed), switches/reloads/checks it and removes only
the transaction journal. It does not clear a failure latch. Then `clear-failure`
and optionally `run` for an intentional same-day retry. Unexpected metadata or a
bad TLS certificate requires operator intervention. Do not renew the certificate
as an automatic recovery action.

Local log rotation caps generated run logs (14 rotations, normal daily/maxsize
rotation); no email recipient or external alert is configured. Failed startup
before valid control metadata exits nonzero; cron stderr is intentionally not
mailed. Verify config and cron exit status during installation. Failure records
otherwise contain only safe step/reason metadata. No automatic release, backup,
old-user-data or failed-job deletion is performed.

## Production scheduler and queue — prepared, disabled

`adsr-jobs.cron.disabled` is a separate every-minute `schedule:run` and bounded
database-queue drain. **Keep it outside `/etc/cron.d` during preview.** `jobs.py`
returns without application boot in preview. Production requires `mode=production`
and a root `0600` `production-approved.json` with all these explicit true gates:
`approved`, `jobs_share_lock`, `mail_ready`, `queue_backlog_reviewed`,
`schedule_approved`. This records the owner's cutover approval plus the parent's
mail/job audit; do not create the record before those decisions. Enabling it is a
future parent operation, not part of this PR's execution.
Production also requires the root operator to explicitly change the persistent
`preview-required.json` to `{"required":false}` after the owner's acceptance.
Changing framework `APP_ENV` or the controller mode alone cannot lift that gate.

The app currently registers daily commands at **00:00 UTC**, in order
`speedruns:disqualifybrokenvideos`, `banner:expire`, `suspensions:expire`.
Video scanning runs inline and can delay the expiry commands; it can create DQ
rows, update category timestamps and queue mail. Banner and suspension expiry
delete expired records; suspension mail is queued first. There are no application
overlap/background guards. Preserve that schedule; the daily 03:30 Chicago deploy
is independent. The host lock prevents deploy/jobs or minute invocations from
overlapping. A missed midnight scheduler invocation does not backfill mutations.
Do not change app timezone or parallelize these commands implicitly.

`QUEUE_CONNECTION=sync` would execute queued mail inline. The prepared production
wrapper therefore refuses it and requires database queue, failed-job driver
`database-uuids`, `retry_after=90`, UTC
and CLI `pcntl`. Before enablement, inspect copied pending/failed backlog locally
and obtain explicit treatment; never blindly replay or delete it. Verify actual
mail transport/recipients under the parent's authorization and audit outbound
video calls, idempotency and retry policy before setting gates.
The current snapshot uses `QUEUE_FAILED_DRIVER=null`; do not enable a worker with
silent failed-job loss. First verify `failed_jobs` schema/UUID compatibility and
explicitly configure the reviewed production failure store without running any
automatic migrations. Preview keeps its current setting and never drains jobs.
Password-reset notifications are synchronous; verify SMTP independently. Config
names alone do not install transport support: this locked production dependency
set has no Mailgun/SES/Postmark bridge or AWS SDK. The video scan calls YouTube
oEmbed serially (3s connect/5s request); provider-health deferral exits successfully.
`last-jobs.json` records successful wrapper steps, not proof of video scan results
or delivered mail. The cutover approval must explicitly cover DQ writes, category
timestamps, expiry deletions, real mail and copied backlog handling.

The minute supervisor starts `queue:work database --queue=default --stop-when-empty
--max-time=45 --timeout=60 --tries=1 --sleep=1 --memory=128`, with the same effective
systemd caps and lock. Timeout 60s is below the 90s retry window. Failed jobs remain
for review; no automatic retry command or purge exists. Scheduler/worker errors
set `job-failure.json` and halt subsequent job runs; after cause/backlog review,
an operator may archive that generated latch manually under the same host lock.
Check `last-jobs.json` locally. This is a cron-supervised short-lived worker, not
an installed persistent worker service. Future additional queues, continuous worker
requirements or longer jobs need review; do not raise resource/runtime caps.
At UTC midnight the runner records `last-midnight.json` as an attempted schedule.
If the lock is busy then, or a later invocation has no recorded midnight attempt,
`missed-midnight.json` flags that day for local operator review. No catch-up or
replay occurs automatically; application logs establish individual command results.

## Validation and references

`Guarded daily deployment` CI uses synthetic Git, SQLite, filesystem and HTTP
fixtures; it covers ancestry/tree mismatch, CI identity/latest attempt, pending
migrations, immutable source, RAM/disk pressure, cross-process exclusion, failure
latches, DST, code rollback with accepted writes and crash recovery. A separate
real systemd test checks effective production caps/non-root execution, OOM behavior
and timed descendant termination. No production data or app dependencies are
needed. The existing full application/browser regression also runs on the exact
PR head. No heavy local Docker or app build is required for this change.

- [Debian cron format and MAILTO](https://manpages.debian.org/bookworm/cron/crontab.5.en.html)
- [systemd-run transient services and wait behavior](https://www.freedesktop.org/software/systemd/man/systemd-run.html)
- [systemd effective resource controls](https://www.freedesktop.org/software/systemd/man/systemd.resource-control.html)
- [GitHub workflow run identities and attempts](https://docs.github.com/en/rest/actions/workflow-runs)
