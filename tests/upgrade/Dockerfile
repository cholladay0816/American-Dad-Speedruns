FROM composer:2.8 AS composer
FROM php:8.5.12-cli-bookworm
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libonig-dev libzip-dev libsqlite3-dev libicu-dev \
    && docker-php-ext-install mbstring pdo_sqlite zip bcmath intl
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
ENV APP_ENV=testing APP_DEBUG=false DB_CONNECTION=sqlite DB_DATABASE=:memory: MAIL_MAILER=array QUEUE_CONNECTION=sync CACHE_DRIVER=array SESSION_DRIVER=array
CMD ["php", "--version"]
