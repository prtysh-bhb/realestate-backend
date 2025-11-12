# -----------------------------------
# Stage 1: PHP Dependencies with Composer
# -----------------------------------
FROM composer:2.7 AS vendor

WORKDIR /app

# Copy only composer files to leverage caching
COPY composer.json composer.lock ./

# Install dependencies (no dev packages for production)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# -----------------------------------
# Stage 2: Application Setup
# -----------------------------------
FROM php:8.3-fpm

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql zip opcache

# Copy composer dependencies from previous stage
COPY --from=vendor /app/vendor /var/www/html/vendor

# Copy application code
WORKDIR /var/www/html
COPY . .

# Set environment to production
ENV APP_ENV=production
ENV APP_DEBUG=false

# Optimize Laravel
RUN php artisan config:clear && php artisan route:clear && php artisan view:clear
RUN php artisan optimize

# Expose port (Railway dynamically assigns $PORT)
EXPOSE 8000

# Run migrations on startup (optional but recommended)
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
