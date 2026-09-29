# Development image for running the test suite. Nothing here is needed at runtime;
# consumers install the library with Composer into their own PHP.
ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-cli-alpine

RUN apk add --no-cache libxml2-dev icu-dev git unzip \
    && docker-php-ext-install soap \
    && apk del libxml2-dev

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
