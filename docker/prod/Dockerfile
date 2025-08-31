# syntax=docker/dockerfile:1

# Base image
FROM php:8.4-fpm-alpine AS base

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip

COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Vendor stage
FROM base AS vendor
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Release stage
FROM base AS release
COPY --from=vendor /var/www/html/vendor/ ./vendor/

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 9000

# Entrypoint
CMD ["php-fpm"]

