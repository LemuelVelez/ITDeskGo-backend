FROM composer:2 AS vendor

WORKDIR /app

RUN apk add --no-cache \
        icu-dev \
        oniguruma-dev \
        libzip-dev \
        zip \
        unzip \
        $PHPIZE_DEPS \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        intl \
        mbstring \
        zip

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

LABEL traefik.docker.network="coolify"

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libonig-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        mbstring \
        mysqli \
        pdo_mysql \
        zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf \
    && printf '%s\n' \
        'ServerName localhost' \
        > /etc/apache2/conf-available/servername.conf \
    && printf '%s\n' \
        '<VirtualHost *:80>' \
        '    ServerAdmin webmaster@localhost' \
        '    DocumentRoot /var/www/html/public' \
        '' \
        '    <Directory /var/www/html/public>' \
        '        Options FollowSymLinks' \
        '        AllowOverride All' \
        '        Require all granted' \
        '        DirectoryIndex index.php index.html' \
        '    </Directory>' \
        '' \
        '    ErrorLog ${APACHE_LOG_DIR}/error.log' \
        '    CustomLog ${APACHE_LOG_DIR}/access.log combined' \
        '</VirtualHost>' \
        > /etc/apache2/sites-available/000-default.conf \
    && a2enconf servername

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

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD php -r '$socket = @fsockopen("127.0.0.1", 80, $errno, $errstr, 2); if ($socket) { fclose($socket); exit(0); } exit(1);'

ENTRYPOINT ["itdeskgo-entrypoint"]
CMD ["apache2-foreground"]
