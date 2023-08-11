ARG COMPOSER_VERSION

#------------------------------------------
#  Common stuff
#------------------------------------------

FROM php:8.1-apache AS common

ARG DEBIAN_FRONTEND=noninteractive

RUN --mount=type=cache,sharing=private,target=/var/cache/apt \
    --mount=type=cache,sharing=private,target=/var/lib/apt \
    apt-get update && \
    apt-get install -y --no-install-recommends locales

COPY ./config/locale.gen /etc
RUN locale-gen

RUN a2enmod rewrite

WORKDIR /var/www

#------------------------------------------
#  For development environment
#------------------------------------------

FROM common AS dev

ARG DEBIAN_FRONTEND=noninteractive

RUN pecl install xdebug

RUN --mount=type=cache,sharing=private,target=/var/cache/apt \
    --mount=type=cache,sharing=private,target=/var/lib/apt \
    apt-get update && \
    apt-get install -y --no-install-recommends unzip

RUN ln -s /var/www/composer.phar /usr/local/bin/composer
RUN ln -s /var/www/phpcs.phar /usr/local/bin/phpcs
RUN ln -s /var/www/vendor/bin/phpunit /usr/local/bin/phpunit

#------------------------------------------
#  For production
#------------------------------------------

#--- Drop development packages and optimize autoloader

FROM composer:${COMPOSER_VERSION} AS foo

COPY vendor /tmp/vendor
COPY composer.json composer.lock /tmp/

WORKDIR /tmp

RUN composer install --no-dev --optimize-autoloader --apcu-autoloader

#--- Build the final image

FROM common AS prod

ARG ROOT=/var/www

# Needed by composer APCu cache
RUN pecl install apcu-5.1.22
RUN docker-php-ext-enable apcu

# Remove the default docroot folder
RUN rm -rf ${ROOT}/html

COPY config/routes.php ${ROOT}/config/
COPY config/php-prod.ini /usr/local/etc/php/conf.d/php.ini
COPY config/apache.conf /etc/apache2/sites-enabled/000-default.conf
COPY src ${ROOT}/src/
COPY --from=foo /tmp/vendor ${ROOT}/vendor
COPY index.php bootstrap.php ${ROOT}/
COPY public ${ROOT}/public
