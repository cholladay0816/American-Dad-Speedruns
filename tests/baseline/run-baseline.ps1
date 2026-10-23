param([switch]$InstallDependencies)
$ErrorActionPreference='Stop'
$repoRoot=(Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
if (Test-Path -LiteralPath (Join-Path $repoRoot '.env')) {
    throw 'Refusing checkout containing .env. This harness requires a synthetic-only checkout.'
}
$mount="type=bind,source=$repoRoot,target=/app"
if ($InstallDependencies) {
    docker run --rm --mount $mount adsr-baseline:php80 composer install --no-interaction --no-scripts --no-plugins --prefer-dist
    if ($LASTEXITCODE -ne 0) { throw 'Baseline dependency installation failed; no tests were run.' }
}
$artifactRoot=Join-Path $PSScriptRoot 'artifacts'
New-Item -ItemType Directory -Force -Path $artifactRoot | Out-Null
docker run --rm --network none --mount $mount adsr-baseline:php80 php tests/baseline/inventory.php > (Join-Path $artifactRoot 'routes.json')
if ($LASTEXITCODE -ne 0) { throw 'Route inventory failed.' }
docker run --rm --network none --mount $mount adsr-baseline:php80 php tests/baseline/smoke.php > (Join-Path $artifactRoot 'sqlite-smoke.json')
if ($LASTEXITCODE -ne 0) { throw 'Synthetic SQLite smoke failed; inspect artifacts/sqlite-smoke.json.' }
