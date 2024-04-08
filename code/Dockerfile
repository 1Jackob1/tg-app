ARG XDEBUG_VERSION=3.3.0
ARG COMPOSER_VERSION=2.7.1

FROM composer:$COMPOSER_VERSION AS composer
FROM php:8.3-fpm-alpine as base
LABEL authors="jackob"

RUN apk add --no-cache \
		acl \
		fcgi \
		file \
		gettext \
		git \
    	bash

COPY --from=composer /usr/bin/composer /usr/local/bin/
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    redis \
    pcntl \
    sockets \
    gd \
    zip


WORKDIR /app

ARG UID=1000
ARG GID=1000
RUN deluser www-data \
    && addgroup -g ${GID} www-data \
    && adduser -u ${UID} -D -G www-data www-data

RUN chown www-data:www-data /app

FROM base AS app_prod

USER www-data

ENV APP_ENV=prod

COPY --chown=www-data --link . ./

RUN composer install --prefer-dist --no-dev --no-scripts --no-progress --classmap-authoritative

FROM base AS app_dev

RUN install-php-extensions \
        pcov \
        xdebug-${XDEBUG_VERSION}

COPY --link docker/php/xdebug.ini $PHP_INI_DIR/conf.d/

USER www-data

COPY --chown=www-data . ./

RUN composer install

FROM caddy:2.7 as caddy

COPY --from=app_dev /app /app

FROM caddy:2.7 as caddy_prod

COPY --from=app_prod /app /app
COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile
