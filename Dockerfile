FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev libzip-dev unzip \
    && docker-php-ext-install mbstring pdo_mysql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist

COPY . .
RUN sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/000-default.conf \
    && mkdir -p public/assets/uploads logs \
    && chown -R www-data:www-data public/assets/uploads logs

EXPOSE 80