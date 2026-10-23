# Laravel 13 / PHP 8.5 upgrade

Development migration from Laravel 8.83.2 to Laravel 13.38.0 on PHP 8.5.12. The existing UI and schema are retained. Billing, council and elections remain retired; historical tables/data remain intact. Verification and 2FA enrollment stay disabled to match the observed application behavior.

## Reproduce isolated tests

Use a synthetic-only checkout without a production `.env`. Install exact lockfiles, then prepare `Dockerfile.single` with `setup-single.sh` as shown in `.github/workflows/laravel.yml`. Run one container at a time with 3 GiB memory, 2 CPUs, one browser worker, no network, and temporary storage/cache mounts.

`sh tests/upgrade/run-single.sh` runs backend tests and the standard five-project browser matrix. Set `ADSR_SESSION_DRIVER=database` and select `browser/gaps.spec.ts` for database-session checks. Set `ADSR_RANKING_FIXTURE=1` and select `browser/ranking.spec.ts` for populated ranking/accessibility checks. This separate fixture is intentionally excluded from the standard screenshot dataset; CI explicitly runs all three stages sequentially and preserves each exit status.

Original synthetic baseline PNGs are committed. Generated logs, databases, screenshots, videos and traces are ignored. Do not update reference images merely to remove failures.

## Recorded local evidence

- Full original matrix: 175 cases, 153 passed, 22 screenshot failures, no skips.
- Auth lifecycle: 20 database-session cases passed. File-session characterization passed 15 cases; five corrected deletion/dialog keyboard cases passed separately.
- Populated ranking/accessibility: 15 passed and five missing iframe-title failures; adding an accessible title passed all five affected cases.
- Backend before the latest video policy: 33 tests, 160 assertions passed. Latest video-policy verification is recorded in the PR.
- Synthetic SQLite forward/restore checks retained 28 tables, canonical rows/schema, integrity OK and zero foreign-key violations.
- Legacy boot under PHP 7.4.33/Laravel 8.83.2 passed four routes against the read-only restored synthetic database. This is not production snapshot or deployment rollback validation.

See `RELEASE-READINESS.md` for gates and `VIDEO-CHECK-REVIEW.md` for the separately reviewable video policy. No production deployment is part of this change. Remaining visual differences are unresolved; the CI screenshot stage is expected to remain red until reviewed.
