# Production preflight and proposed cutover

Status: planning only, October 24, 2026. No production runtime, database,
services, DNS, permissions or resources have been changed. Deployment waits for
the owner's PR approval and the operational gates below.

## Findings and blockers

- The approved operator's read-only Lightsail lookup confirms the original
  Virginia instance is running, with its static address, PHP 7 LAMP blueprint,
  one CPU, 0.5 GiB RAM and 20 GiB system disk. Detailed account identifiers are
  intentionally excluded from this public document.
- Prior inspection recorded Debian 10, Bitnami PHP 7.4.29 and Laravel 8.83.2.
  These host/runtime details were not freshly rechecked over SSH in this
  preflight: no AWS browser terminal is currently open. No temporary keys were
  requested or printed. An authenticated operator terminal must be restored
  before the remaining host inspection can be completed.
- The previously observed snapshot is not proof of a current consistent backup
  or a tested restore. Snapshot API access was previously denied; it was not
  retried or bypassed. Completion time, source, state and recovery must be
  verified through an authorized route before deployment.
- The existing Ohio candidate is NOT verified as a deployment destination.
  Do not infer its PHP version from its name, move regions, purchase resources,
  or reuse it without explicit target selection and runtime inspection.
- Actual application database driver/path remains a deployment gate. The repo
  defaults to MySQL, supports SQLite, and the tests use SQLite; running MariaDB
  on the host alone does not establish the application's selected driver.
- Actual uploads, filesystem roots, symlinks, ownership, PHP-FPM socket/handler,
  Apache vhosts, TLS renewal, cron, queue workers and session/cache drivers
  require a narrowly filtered host inventory. Do not dump environment files,
  credentials, personal records, application logs or arbitrary config files.
- The repository's local disk points to storage/app; its public disk points to
  storage/app/public with a public/storage link. These are source defaults, not
  confirmation of live upload locations. Preserve any additional live assets,
  untracked files and local authentication behavior; never git clean/reset them.

## Runtime choice

Do not run apt upgrade expecting it to replace Bitnami PHP. AWS explicitly
documents that Bitnami has its own /opt/bitnami/php and describes a separate FPM
pool/socket plus web-server integration for a package-managed replacement.
Its generic instructions preserve the old PHP release line; they are not a
validated PHP 7.4-to-8.5 migration recipe for this host.

Debian 10's Debian LTS ended June 2024. Sury's public site directs Buster users
to a commercial repository, so an available supported, no-paid-patch PHP 8.5
installation source for this exact OS has NOT been established. Preferred
proposal: select a supported-OS target with a maintained PHP 8.5 FPM/CLI source,
then rehearse it. Any new instance or regional move needs separate approval.
Do not replace the live OS or build PHP on the tiny live instance as an
unreviewed workaround.

The candidate requires PHP ^8.5 and was tested on 8.5.12. Verify the final
package/image provenance, patch version and digest before staging. Check CLI
and serving FPM separately. Required framework extensions include ctype, curl,
dom, fileinfo, filter, hash, mbstring, openssl, pcre, PDO, session, tokenizer and
xml, plus the actual DB PDO driver and every locked package requirement.
Run composer check-platform-reqs --no-dev against the target artifact; do not
use --ignore-platform-reqs. The legacy ext-http root requirement was removed;
do not add it blindly. Verify OPcache, upload limits, timezone, FPM user/socket
permissions and pool memory sizing. Do not copy example worker counts onto a
0.5 GiB server. Build Composer/Vite artifacts away from production.

## Release candidate gate

The merged upgrade and tested follow-up are distinct source states. Include
the reviewed reset-test and modal-focus fixes in an immutable release commit.
Focused local coverage is 40/40 browser tests across five projects and two
session modes, plus 34 backend tests / 187 assertions in each run. This is not
a green full matrix. Preserve the 22 original visual comparison failures;
the owner's favorable preview review is useful acceptance evidence but does
not automatically approve every reference replacement. Record specific visual
decisions and rerun the integrated CI suite against the final commit.

Keep billing/council retired, retain historical data and disabled verification/
2FA behavior, and review video-disqualification behavior separately. Confirm
real mail/video integrations in approved staging without affecting real users.

## Proposed cutover sequence (not executed)

1. **Inventory and recovery gate.** Through the approved operator, resolve the
   effective DB driver and file/socket path without returning secrets. Record
   binary versions, modules, vhost document root and handler/socket, service
   ownership, persistent file paths, scheduler/worker commands and available
   memory/disk. Inspect only relevant directives. Preserve APP_KEY, DB/mail
   settings and TLS material in their authorized host location; do not rotate
   keys or copy production secrets/data into development or CI.
2. **Staging gate.** On an approved target matching the intended deployment,
   install the immutable release beside the old release. Serve only public/,
   deny hidden/private files, verify writable storage/cache, FPM socket and
   explicit CLI paths for cron/workers. Use synthetic data to test Apache/FPM,
   authentication, submissions, moderation, uploads, queues and rollback.
   The existing local old-runtime rehearsal covers only four routes with a
   synthetic read-only DB; it does not prove production recovery.
3. **Prebuild and preflight.** Have both old/new code, vendors and assets ready
   before the maintenance window. Verify backup/restore procedure, migration
   list and capacity. No dependency update or asset build during downtime.
   Define who approves reopen/rollback and measure timings in rehearsal; no
   credible numeric downtime estimate exists yet.
4. **Write freeze.** At the announced window, put web writes behind maintenance,
   pause scheduler-driven mutations, drain/pause queue consumers and block other
   write entry points. Verify no background writer remains. Keep workers from
   both releases from running concurrently. Record the last accepted-write time.
5. **Consistent recovery point.** Take a fresh authorized snapshot and an
   engine-appropriate consistent DB backup plus persistent uploads/config
   backup. For SQLite use its backup mechanism with WAL handled, not a lone
   live .sqlite copy. For MySQL/MariaDB verify engine/transaction consistency
   and backup tooling. Verify hashes, completeness and tested restoration;
   never expose backup contents in chat or the PR.
6. **Switch and validate while writes remain closed.** Apply only reviewed
   pending migrations if any, switch the complete code/vendor/assets and
   matching FPM handler, rebuild target-specific caches, validate configuration
   and perform the approved graceful service transition. Use explicit new PHP
   for workers/cron. Check HTTPS, login, read views, permissions, DB integrity,
   storage and critical synthetic/safe health checks before reopening.
7. **Reopen and observe.** Enable only intended workers/schedules, restore writes
   and monitor errors/latency/memory/provider health. Maintain old artifacts and
   backups for the agreed retention period; no immediate old-server deletion.

## Rollback rule

Before writes reopen, switch code/vendor/assets, PHP handler and service paths
back together; restore the verified pre-change DB/files when required and check
old-runtime health before admitting traffic. Do not assume migrate:rollback is
a backup. After any new writes, restoring an old DB would lose data: freeze
again, preserve new writes and choose an explicit reconciliation/forward-fix
plan. A cross-region target also changes the traffic-switch plan; it is not an
implicit same-region static-IP reassignment.

## Official references checked

- [AWS: Bitnami PHP differs from package-managed PHP](https://docs.aws.amazon.com/lightsail/latest/userguide/amazon-lightsail-update-php-security-vulnerabilities.html)
- [Debian 10 LTS end](https://www.debian.org/News/2024/20240615)
- [Sury repository information](https://deb.sury.org/)
- [Laravel 13 deployment requirements](https://laravel.com/docs/13.x/deployment)
- [Bitnami PHP-FPM configuration paths](https://docs.bitnami.com/general/infrastructure/lamp/configuration/configure-phpfpm-options/)
