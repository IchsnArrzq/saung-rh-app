#!/bin/sh
set -e

APP_DIR="/var/www/html"
cd "$APP_DIR"

mkdir -p \
  storage/app/public \
  storage/logs \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  bootstrap/cache

touch storage/logs/laravel.log || true
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwx storage bootstrap/cache || true

if [ -f artisan ]; then
  rm -f bootstrap/cache/*.php || true

  if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
  fi

  if [ -f .env ] && ! grep -Eq '^APP_KEY=base64:.+' .env; then
    php artisan key:generate --force --no-interaction || true
  fi

  php artisan storage:link || true
  php artisan config:clear || true
  php artisan package:discover --ansi || true
fi

exec "$@"
