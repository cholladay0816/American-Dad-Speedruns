set -eu
export ADSR_AUTH_BASELINE=source
mkdir -p /app/storage/framework/cache/data /app/storage/framework/sessions /app/storage/framework/views /app/storage/logs
export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
touch /tmp/adsr-baseline.sqlite
php /app/tests/baseline/seed-browser.php
php /app/tests/baseline/seed-roles.php
exec php -S 127.0.0.1:8085 -t /app/public /app/tests/baseline/router.php
