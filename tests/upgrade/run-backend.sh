set -eu
cd /app
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
export ADSR_AUTH_BASELINE=source
php vendor/bin/phpunit --log-junit tests/baseline/artifacts/video-review-final.xml
