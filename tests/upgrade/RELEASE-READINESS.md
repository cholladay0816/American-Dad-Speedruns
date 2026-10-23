# Release-readiness checklist

Local verification is complete for the bounded scope below. Production rollout is not approved. This pre-publication evidence was recorded on `codex/laravel13-php85`, based on `44917962a8f644a1e05a20e66fb19db7bf9851b1`.

## Verified locally

- [x] PHP 8.5.12 and Laravel 13.38.0 development runtime. Earlier locked installs, audits and build evidence are retained; the final coverage phase added no dependencies.
- [x] Backend: **33 tests, 160 assertions passed**. New checks cover competition ties (1, 1, 3), five-decimal ordering, disqualified/unverified filtering, invalid requests without partial writes or mail, minimum accepted time, and missing-resource 404s.
- [x] Original full browser matrix: **153 passed, 22 screenshot failures, zero skips**. Subsequent targeted runs are separate evidence; the entire latest suite has not run together on the final source.
- [x] Auth lifecycle: 20 cases passed with database sessions. File-session characterization passed 15 cases; corrected deletion and dialog keyboard tests passed all five projects. Coverage includes reset-token reuse rejection, deletion password checks, session revocation, retained current session, and keyboard/error navigation.
- [x] Populated ranking/accessibility: the 20-case run passed 15 and found five missing iframe-title failures. The accessible-title fix passed all five affected checks. Original evidence is retained; goldens are unchanged. Category, run-detail and profile scans cover serious/critical axe findings, not exhaustive accessibility certification.
- [x] Existing no-pagination behavior: all 25 rows remain visible on both the ordinary list and `?page=2`. Category lists still include unverified runs while the global list excludes them. Tests characterize this discrepancy; no new pagination or filtering policy was added.
- [x] Video response mocks, scheduler idempotence and a database queue worker delivering only to array mail passed. No live provider traffic, payments or mail.
- [x] Synthetic SQLite forward rehearsal retained 28 tables and canonical rows/schema. Restored copy is byte-identical, with integrity OK and zero foreign-key violations.
- [x] Legacy boot: pinned Laravel 8.83.2 source under official PHP 7.4.33 returned 200 for `/login`, `/speedruns`, `/categories/synthetic`, and `/watch/1`. The restored synthetic database was mounted read-only, networking disabled, hash unchanged, and no mail queued. See `artifacts/rehearsal/boot/legacy-boot.json`.
- [x] CI configuration validates locally. File-session, database-session and populated-fixture suites run sequentially, with every failure propagated. Remote CI has not been dispatched.
- [x] All containers and guards stopped. Final-phase peak memory: 66.5%; final: 65%. Maximum one container, capped at 3 GiB and 2 CPUs, with the 85% guard intact.

## Review gates

- [ ] Review and accept intended visual changes using the 22-case classification and before/after contact sheet. Resolve residual rendering differences before approving specific reference changes. Do not replace all goldens to make tests pass. Private visual review supplied separately.
- [ ] Review `VIDEO-CHECK-REVIEW.md` separately. The requested video classification and batch-health safeguards are business behavior, not a Laravel migration requirement.
- [ ] Review the documented category/global filtering discrepancy, unpaginated behavior and dead admin links. Do not restore retired features or add unrelated functionality to complete this migration.
- [ ] Run a fresh integrated matrix against the final reviewed source and approved references, followed by remote CI after separately authorized publication/workflow execution. Multiple passing targeted runs are not proof of zero breakage.

## Requires later staging or operational authorization

- [ ] Verify YouTube metadata/embed behavior and mail delivery through approved staging or sandbox routes. Billing and council stay retired; payment-provider integration is not a release milestone.
- [ ] Verify target Apache/PHP-FPM configuration, extensions, storage permissions, scheduler/workers and release packaging. CLI containers do not establish production host compatibility.
- [ ] Rehearse full staging rollback, including authenticated flows, runtime/assets switching and consistent backup/restore. The local test covers four routes on PHP 7.4.33; it is not the exact Bitnami PHP 7.4.29 stack or proof of production snapshot validity.
- [ ] Establish a write freeze and reconciliation plan. Restoring an old database after new writes would lose data. Record recovery timings and observed outcomes before rollout.
- [ ] Obtain separate authorization for provisioning, production access, deployment, merging or paid resources. None occurred in this task.

Remaining limits are manual screen-reader/keyboard review beyond exercised flows, exhaustive role/route/error combinations, production-scale performance, real provider fidelity and exact production recovery. Profile photos, API and teams remain disabled. Historical billing, council and 2FA data is retained.
