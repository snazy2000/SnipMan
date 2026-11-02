# Dockerfile for Laravel web and queue pods
#
# PHP is pinned to an exact patch release. 8.4.27 is the first 8.4 build with
# fixes for CVE-2026-109618 and CVE-2026-109619 (unauthenticated RCE, CVSS 9.2,
# both in the CISA KEV catalog). Do not relax this to a floating 8.4 tag.
FROM php:8.4.27-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    zlib-dev \
    g++ \
    make \
    autoconf \
    openssl-dev \
    nodejs \
    npm \
    postgresql-dev

# Install PHP extensions (including Postgres)
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip exif pcntl bcmath intl gd

# Harden the PHP runtime.
#
# session.upload_progress is the confirmed cross-request vector for
# CVE-2026-109619 and the only published mitigation for CVE-2026-115560, which
# has no upstream fix as of 2026-11-02. It is enabled by default and does not
# require session_start(). It must be disabled in php.ini - ini_set() is too
# late to help.
RUN printf 'session.upload_progress.enabled=0\nexpose_php=0\n' \
    > /usr/local/etc/php/conf.d/99-security.ini

# Fail the build rather than ship a PHP that is still vulnerable.
RUN php -r 'exit(version_compare(PHP_VERSION, "8.4.27", ">=") ? 0 : 1);'

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Install JS dependencies and build assets (optional for queue)
RUN npm install && npm run build || true

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Entrypoint for queue worker
CMD ["php", "artisan", "queue:work", "--verbose", "--tries=3", "--timeout=90"]
