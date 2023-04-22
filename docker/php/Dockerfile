FROM php:8.1.18-fpm-alpine3.17

RUN apk update; \
    apk upgrade;
RUN docker-php-ext-install mysqli pdo pdo_mysql xdebug
