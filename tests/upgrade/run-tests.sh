set -eu
mkdir -p /app/storage/framework/cache/data /app/storage/framework/sessions /app/storage/framework/views /app/storage/logs
export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
exec php /app/vendor/bin/phpunit "$@"
