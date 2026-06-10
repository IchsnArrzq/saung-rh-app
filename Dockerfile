# ==================================
# Frontend Build Stage
# ==================================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# ==================================
# PHP Stage
# ==================================
FROM php:8.2-fpm

WORKDIR /var/www/html

ENV APP_ENV=production
ENV APP_DEBUG=false

COPY docker/php/custom.ini /usr/local/etc/php/conf.d/zzz-custom.ini
COPY docker/php/production.ini /usr/local/etc/php/conf.d/zzzz-production.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zzz-opcache.ini
COPY docker/php/entrypoint.sh /usr/local/bin/app-entrypoint

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        ghostscript \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        gd \
        intl \
        opcache \
        pdo_pgsql \
        pgsql \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

COPY . .

# hasil build vite
COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload \
    --no-dev \
    --optimize \
    --classmap-authoritative \
    --no-interaction \
    --no-scripts \
    && rm -f bootstrap/cache/*.php \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache \
    && chmod +x /usr/local/bin/app-entrypoint

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/app-entrypoint"]
CMD ["php-fpm"]