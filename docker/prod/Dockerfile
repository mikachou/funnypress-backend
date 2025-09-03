# syntax=docker/dockerfile:1

# ============================
# Stage 1: Node builder (for Vite / Mix)
# ============================
FROM node:20-alpine AS node_builder

WORKDIR /app

# Install dependencies
COPY package*.json vite.config.* ./
RUN npm install

# Copy source files
COPY resources ./resources
COPY public ./public

# Build production assets
RUN npm run build

# ============================
# Stage 2: PHP-FPM / Laravel app
# ============================

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

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Vendor stage
FROM base AS vendor
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# Release stage
FROM base AS release
WORKDIR /var/www/html
COPY . .
COPY --from=vendor /var/www/html/vendor /var/www/html/vendor
COPY --from=node_builder /app/public/build ./public/build

COPY .env.production .env
COPY .env.db.prod .env.db

RUN composer run-script post-autoload-dump || true

# Run Filament asset publishing
RUN php artisan filament:assets --ansi --no-interaction

# Laravel optimizations
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan storage:link || true

RUN rm -f .env .env.db

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 9000

# Entrypoint
CMD ["php-fpm"]