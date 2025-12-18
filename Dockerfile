# Dockerfile for Laravel web and queue pods
FROM php:8.4-fpm-alpine

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

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

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
