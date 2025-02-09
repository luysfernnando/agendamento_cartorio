FROM php:8.3-fpm

LABEL maintainer="getlaminas.org" \
    org.label-schema.docker.dockerfile="/Dockerfile" \
    org.label-schema.name="Laminas MVC Skeleton" \
    org.label-schema.url="https://docs.getlaminas.org/mvc/" \
    org.label-schema.vcs-url="https://github.com/laminas/laminas-mvc-skeleton"

## Update package information
RUN apt-get update

## Install Composer
RUN curl -sS https://getcomposer.org/installer \
  | php -- --install-dir=/usr/local/bin --filename=composer

## Install zip libraries and extension
RUN apt-get install --yes git zlib1g-dev libzip-dev \
    && docker-php-ext-install zip

## Install intl library and extension
RUN apt-get install --yes libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

## Install MySQL PDO support
RUN docker-php-ext-install pdo_mysql

## Install PostgreSQL PDO support
# RUN apt-get install --yes libpq-dev \
#     && docker-php-ext-install pdo_pgsql

## Install APCU
# RUN pecl install apcu \
#     && docker-php-ext-enable apcu

## Install Memcached
# RUN apt-get install --yes libmemcached-dev \
#     && pecl install memcached \
#     && docker-php-ext-enable memcached

## Install MongoDB
# RUN pecl install mongodb \
#     && docker-php-ext-enable mongodb

## Install Redis support.  igbinary and libzstd-dev are only needed based on 
## redis pecl options
# RUN pecl install igbinary \
#     && docker-php-ext-enable igbinary \
#     && apt-get install --yes libzstd-dev \
#     && pecl install redis \
#     && docker-php-ext-enable redis

WORKDIR /var/www
