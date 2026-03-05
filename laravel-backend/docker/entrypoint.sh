#!/bin/sh
set -e

echo "==> Running Laravel optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Seeding database (if needed)..."
php artisan db:seed --force --class=AdminSeeder 2>/dev/null || true

echo "==> Creating storage link..."
php artisan storage:link 2>/dev/null || true

echo "==> Starting Supervisor (PHP-FPM + Nginx)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
