set -eu
apt-get update
apt-get install -y --no-install-recommends git unzip libonig-dev libzip-dev libsqlite3-dev libicu-dev
docker-php-ext-install -j2 mbstring pdo_sqlite zip bcmath intl
node /opt/playwright-core/cli.js install --with-deps chromium firefox webkit
