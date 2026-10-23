# Local CI and rollback review — development only

## Reviewed CI boundaries
The workflow uses a read-only repository token and commit-pinned checkout/upload actions. Dependency setup and test execution are sequential, capped at 2 CPUs and at most 3 GiB. Browser/app/backend share one network-isolated container with one browser worker. Package downloads occur only during explicit dependency setup. PHP mail uses array transport; provider calls are mocked. Original screenshot assertions remain active and currently fail; CI must not be labelled green or bypassed by replacing goldens wholesale. YAML and local build/lock/backend commands were exercised; GitHub CI has NOT been run.

The Windows memory guard is local orchestration, not a claimed GitHub runner guarantee. Stop only the task container if Windows use reaches85% or another container starts. CI evidence must be treated as potentially containing synthetic sessions; publish only reviewed sanitized visual reports. Pin the final built runtime digest before staging; mutable version tags alone are not complete reproducibility.

## Synthetic database evidence and rollback boundary
The existing populated Laravel8 fixture and Laravel13 forward rehearsal retained28tables with exact canonical rows/schema, integrity OK and zero foreign-key violations. Historical billing/council/2FA data was retained. No destructive cleanup migration was added.
A separate restored-legacy-copy.sqlite was copied from legacy-original.sqlite without overwriting either source. SHA256 matches: 2FA99540D79B4FFC48F8B5DEF18E1DBEBFDBBD89722963FFE00C165724056D10. See artifacts/rehearsal/rollback-copy-verification.json. This rehearses byte recovery ONLY; old-runtime boot, deployment switching, post-upgrade writes and production backup restoration remain unverified. The fixture contains synthetic records only and must not be mistaken for a production backup.

## Required separately authorized staging rehearsal
1. Use an isolated staging deployment and fresh synthetic dataset. Build immutable old/new artifacts from the reviewed commits and locks; record hashes, PHP extensions and frontend asset manifests.
2. Verify baseline application behavior and DB integrity. Capture an immutable SQLite backup using a consistency-safe SQLite backup mechanism with writes quiesced; copy user files only in a future explicitly authorized staging scope. Never copy production secrets/data into this harness.
3. Rehearse the upgrade with writes stopped, exact migration status captured, and a health gate covering login, submissions, authorization, moderation, queue/scheduler and retired surfaces. Run the approved visual comparisons. Keep old code/runtime/assets recoverable.
4. Rehearse failure before writes reopen: switch back to old code/runtime/assets and restore the verified pre-upgrade database snapshot. Boot and recheck login, row/schema integrity and critical workflows. Do not use migrate:rollback as a substitute for verified database recovery.
5. If writes occurred after upgrade, stop and plan explicit write reconciliation; restoring the old DB would lose those writes. Do not silently discard data. Rehearse this decision before rollout.
6. Record timings, hashes and checks. Require review of staging/rollback evidence and a separate production rollout authorization. Current local tests do not establish production readiness.

## Explicit behavior decisions needing review
- Existing verification and2FA enrollment remain disabled. Historical columns are retained.
- File-backed sessions cannot be revoked through Jetstream's other-browser-session action: its upstream implementation returns early. The browser test characterizes this limitation. Database-backed session revocation is tested separately; no production session configuration was changed.
- Legacy video lookup incorrectly treated transport failure as existence. The latest separately reviewed behavior is documented in VIDEO-CHECK-REVIEW.md; it classifies bounded 401/403/404/410 evidence only with batch-health safeguards. Provider behavior is mocked; no YouTube access or live disqualification occurred. This semantic correction requires review before rollout.
- Existing admin links without routes, broader keyboard/screen-reader behavior, ranking/ties and external provider rendering remain concrete coverage limits.

Update: the restored copy was opened read-only with PHP8.5 PDO SQLite. Integrity returned ok,28tables,zero foreign-key violations. This still does not verify old-runtime boot. Database-session gap matrix completed20/20 with0skips/errors on allfive browser projects; corrected file-session deletion/keyboard checks are being recorded separately.

Latest: old Laravel8.83.2 boot underPHP7.4.33 now verified for four local read-only syntheticDB routes; see RELEASE-READINESS.md and artifacts/rehearsal/boot/legacy-boot.json. Exact production stack, authenticated rollback, deployment switching and real snapshot recovery remain unverified.
