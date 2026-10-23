FROM composer:2.8 AS composer
FROM php:8.0-cli-alpine3.16
RUN apk add --no-cache $PHPIZE_DEPS git unzip oniguruma-dev libzip-dev sqlite-dev curl-dev libevent-dev icu-dev openssl-dev linux-headers \
    && docker-php-ext-install mbstring pdo_sqlite zip bcmath intl \
    && pecl install raphf-2.0.2 && docker-php-ext-enable raphf \
    && pecl install pecl_http-4.2.6 && docker-php-ext-enable http
RUN mv /usr/local/etc/php/conf.d/docker-php-ext-http.ini /usr/local/etc/php/conf.d/zz-http.ini
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
ENV APP_ENV=testing APP_DEBUG=false DB_CONNECTION=sqlite DB_DATABASE=:memory: MAIL_MAILER=array QUEUE_CONNECTION=sync CACHE_DRIVER=array SESSION_DRIVER=array
CMD ["php", "--version"]
