#!/bin/sh
set -e

echo "==> Running Laravel optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Waiting for API service to run migrations..."
# Dashboard doesn't run migrations — the API service handles that.
# Just wait for the DB to be reachable.
until php artisan db:monitor --databases=mysql 2>/dev/null; do
    echo "    Waiting for database..."
    sleep 3
done

echo "==> Creating storage link..."
php artisan storage:link 2>/dev/null || true

echo "==> Starting Supervisor (PHP-FPM + Nginx)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
