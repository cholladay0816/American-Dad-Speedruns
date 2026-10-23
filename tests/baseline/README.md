# Synthetic baseline harness

Characterizes the original Laravel application using disposable SQLite fixtures, fake mail/notifications and blocked external browser requests. The original source commit is recorded in `coverage-matrix.json`.

The committed PNGs are synthetic reference images, not production screenshots. Generated reports, traces, videos, databases and local operational notes are excluded from Git. Test fixture accounts and passwords are intentionally synthetic and must never be reused for production.

See `../upgrade/README.md` and the CI workflow for the current capped, single-container execution commands. `run-baseline.ps1` is a historical baseline helper; use the current upgrade workflow for resource-limited runs. No test may point at production or retrieve production credentials/data.
