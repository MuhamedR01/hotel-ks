#!/bin/sh
set -e

# SERVICE_ROLE: "api", "dashboard", or "combined" (default)
ROLE=${SERVICE_ROLE:-combined}
echo "==> Service role: $ROLE"

# ── Select Nginx config based on role ────────────────────────
if [ -f "/tmp/nginx-${ROLE}.conf" ]; then
    echo "==> Using nginx-${ROLE}.conf"
    cp "/tmp/nginx-${ROLE}.conf" /etc/nginx/http.d/default.conf
else
    echo "==> WARNING: nginx-${ROLE}.conf not found, using combined"
    cp /tmp/nginx-combined.conf /etc/nginx/http.d/default.conf
fi

# ── Laravel optimizations ────────────────────────────────────
echo "==> Running Laravel optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ── Database: migrations + seeding (api/combined only) ───────
if [ "$ROLE" = "api" ] || [ "$ROLE" = "combined" ]; then
    echo "==> Waiting for database and running migrations..."
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
else
    echo "==> Dashboard role — skipping migrations (API handles them)."
    echo "==> Waiting for database to be reachable..."
    MAX_TRIES=30
    COUNT=0
    until php artisan db:monitor --databases=mysql 2>/dev/null; do
        COUNT=$((COUNT + 1))
        if [ $COUNT -ge $MAX_TRIES ]; then
            echo "ERROR: Database not reachable after $MAX_TRIES attempts. Exiting."
            exit 1
        fi
        echo "    Database not ready (attempt $COUNT/$MAX_TRIES). Retrying in 5s..."
        sleep 5
    done
fi

echo "==> Creating storage link..."
php artisan storage:link 2>/dev/null || true

echo "==> Starting Supervisor (PHP-FPM + Nginx)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
