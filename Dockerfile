# syntax=docker/dockerfile:1

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-autoloader --no-scripts
COPY src/ src/
RUN composer dump-autoload --no-dev --classmap-authoritative

FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite \
    && sed -i 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i -e 's/^ServerTokens .*/ServerTokens Prod/' -e 's/^ServerSignature .*/ServerSignature Off/' /etc/apache2/conf-available/security.conf \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf 'expose_php = Off\nsession.cookie_httponly = 1\nsession.use_strict_mode = 1\n' > "$PHP_INI_DIR/conf.d/zz-hardening.ini"
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/app
COPY --from=vendor /app/vendor/ vendor/
COPY src/ src/
COPY templates/ templates/
COPY public/ public/

USER www-data
EXPOSE 8080
HEALTHCHECK --interval=10s --timeout=3s --retries=5 CMD php -r 'exit(@file_get_contents("http://127.0.0.1:8080/") === false ? 1 : 0);'
