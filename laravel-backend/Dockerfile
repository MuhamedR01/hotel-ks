# ──────────────────────────────────────────────────────────────
#  Laravel Backend — multi-stage Dockerfile
#  Produces a production-ready PHP-FPM + Nginx image.
# ──────────────────────────────────────────────────────────────

# ── Stage 1: Composer dependencies ───────────────────────────
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist

# ── Stage 2: Production image ────────────────────────────────
FROM php:8.3-fpm-alpine AS production

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        intl \
        bcmath \
        pcntl \
    && rm -rf /var/cache/apk/*

# PHP production settings
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "upload_max_filesize = 10M" >> "$PHP_INI_DIR/conf.d/uploads.ini" \
    && echo "post_max_size = 12M" >> "$PHP_INI_DIR/conf.d/uploads.ini" \
    && echo "memory_limit = 256M" >> "$PHP_INI_DIR/conf.d/uploads.ini"

# Which service: "api", "dashboard", or "combined" (default)
ARG SERVICE_ROLE=combined

WORKDIR /var/www/html

# Copy application files
COPY . .
COPY --from=vendor /app/vendor ./vendor

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Nginx config — pick the right one based on SERVICE_ROLE
COPY docker/nginx.conf        /tmp/nginx-combined.conf
COPY docker/nginx-api.conf    /tmp/nginx-api.conf
COPY docker/nginx-dashboard.conf /tmp/nginx-dashboard.conf
RUN cp /tmp/nginx-${SERVICE_ROLE}.conf /etc/nginx/http.d/default.conf

# Supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose port 80
EXPOSE 80

# Entrypoint — API/combined runs migrations, dashboard does not
COPY docker/entrypoint.sh           /tmp/entrypoint-combined.sh
COPY docker/entrypoint.sh           /tmp/entrypoint-api.sh
COPY docker/entrypoint-dashboard.sh /tmp/entrypoint-dashboard.sh
RUN cp /tmp/entrypoint-${SERVICE_ROLE}.sh /entrypoint.sh \
    && chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
