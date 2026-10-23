set -eu
cd /app
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
export ADSR_AUTH_BASELINE=source
DB_DATABASE=:memory: SESSION_DRIVER=array php vendor/bin/phpunit --log-junit tests/baseline/artifacts/single-phpunit.xml > tests/baseline/artifacts/single-phpunit.log 2>&1
export DB_DATABASE=/tmp/adsr-baseline.sqlite SESSION_DRIVER=${ADSR_SESSION_DRIVER:-file} APP_URL=http://127.0.0.1:8085
touch "$DB_DATABASE"
php tests/baseline/seed-browser.php
php tests/baseline/seed-roles.php
php tests/baseline/seed-gaps.php
if [ "${ADSR_RANKING_FIXTURE:-0}" = "1" ]; then php tests/baseline/seed-ranking.php; fi
php -S 127.0.0.1:8085 -t public tests/baseline/router.php > tests/baseline/artifacts/single-server.log 2>&1 &
server=$!
trap 'kill "$server" 2>/dev/null || true' EXIT INT TERM
for attempt in 1 2 3 4 5; do
 if php -r 'exit(@file_get_contents("http://127.0.0.1:8085/login")===false?1:0);'; then break; fi
 sleep 1
done
cd tests/baseline
node node_modules/playwright/cli.js test --workers=1 "$@"
