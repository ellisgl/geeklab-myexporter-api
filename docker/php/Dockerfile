FROM php:8.1.18-fpm-alpine3.17

RUN apk update; \
    apk upgrade;

RUN apk add --no-cache sqlite-libs
RUN apk add --no-cache icu sqlite git zip openssh
RUN apk add --no-cache --virtual .build-deps icu-dev libxml2-dev sqlite-dev curl-dev
RUN docker-php-ext-install bcmath curl mysqli pdo pdo_mysql pdo_sqlite
RUN apk del .build-deps

RUN docker-php-ext-enable pdo_sqlite

# Add xdebug
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS
RUN apk add --update linux-headers
RUN pecl install xdebug-3.2.1
RUN docker-php-ext-enable xdebug
RUN apk del -f .build-deps

# Configure Xdebug
RUN echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.log=/var/www/html/xdebug/xdebug.log" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.discover_client_host=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_port=9000" >> /usr/local/etc/php/conf.d/xdebug.ini
