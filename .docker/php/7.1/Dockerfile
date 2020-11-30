FROM php:7.1-fpm-alpine
ARG XDEBUG_VERSION="2.9.8"
ARG PHPREDIS_VERSION="5.3.2"
RUN set -ex \
	&& apk update \
	&& apk upgrade --available \
	&& apk add --no-cache $PHPIZE_DEPS icu-dev \
	&& docker-php-ext-install intl \
	&& mkdir -p "/usr/src/php/ext/xdebug" \
	&& curl -fsSL "https://pecl.php.net/get/xdebug-${XDEBUG_VERSION}" | tar xvz -C "/usr/src/php/ext/xdebug" --strip 1 \
	&& echo 'xdebug' >> "/usr/src/php-available-exts" \
	&& docker-php-ext-install xdebug \
	&& rm -rf "/usr/src/php/ext/xdebug" \
	&& mkdir -p "/usr/src/php/ext/redis" \
    && curl -fsSL "https://github.com/phpredis/phpredis/archive/${PHPREDIS_VERSION}.tar.gz" | tar xvz -C "/usr/src/php/ext/redis" --strip 1 \
    && echo 'redis' >> "/usr/src/php-available-exts" \
    && docker-php-ext-install redis \
    && rm -rf "/usr/src/php/ext/redis" \
    && apk del $PHPIZE_DEPS \
    && rm -rf /tmp/* /var/cache/apk/*

COPY --from=composer /usr/bin/composer /usr/bin/composer
ADD "https://phar.phpunit.de/phpunit-7.phar" /usr/bin/phpunit

RUN chmod +x /usr/bin/composer /usr/bin/phpunit
