FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts

COPY . .
RUN composer dump-autoload \
    --no-dev \
    --optimize \
    --no-interaction

FROM php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install \
        intl \
        mbstring \
        mysqli \
        pdo_mysql \
        zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/codeigniter.conf \
    && a2enconf codeigniter

COPY --from=vendor /app ./
COPY docker/entrypoint.sh /usr/local/bin/itdeskgo-entrypoint

RUN chmod +x /usr/local/bin/itdeskgo-entrypoint \
    && mkdir -p \
        writable/cache \
        writable/debugbar \
        writable/logs \
        writable/session \
        writable/uploads \
    && chown -R www-data:www-data writable \
    && chmod -R ug+rwX writable

EXPOSE 80

ENTRYPOINT ["itdeskgo-entrypoint"]
CMD ["apache2-foreground"]
