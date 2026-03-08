#!/bin/sh
set -e

echo "==> Running Laravel optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Waiting for database to be reachable..."
MAX_TRIES=30
COUNT=0
until php artisan migrate --force 2>/dev/null; do
    COUNT=$((COUNT + 1))
    if [ $COUNT -ge $MAX_TRIES ]; then
        echo "ERROR: Database not reachable after $MAX_TRIES attempts. Exiting."
        exit 1
    fi
    echo "    Database not ready (attempt $COUNT/$MAX_TRIES). Retrying in 5s..."
    sleep 5
done
echo "==> Migrations complete."

echo "==> Seeding database (if needed)..."
php artisan db:seed --force --class=AdminSeeder 2>/dev/null || true

echo "==> Creating storage link..."
php artisan storage:link 2>/dev/null || true

echo "==> Starting Supervisor (PHP-FPM + Nginx)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
