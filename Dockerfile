# -----------------------------------
# Stage 1: Base PHP + extensions
# -----------------------------------
FROM php:8.3-fpm AS base

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql zip opcache \
    && rm -rf /var/lib/apt/lists/*

# Copy Composer from official image
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# -----------------------------------
# Stage 2: Build App
# -----------------------------------
FROM base AS build

WORKDIR /var/www/html

# Copy everything (so artisan exists before composer runs)
COPY . .

# Install PHP dependencies (handle potential lock mismatch safely)
RUN composer install --no-dev --optimize-autoloader --no-interaction || composer install --ignore-platform-reqs

# Optimize Laravel
RUN php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan optimize

# -----------------------------------
# Stage 3: Production Container
# -----------------------------------
FROM base AS production

WORKDIR /var/www/html

# Copy built app and vendor from previous stage
COPY --from=build /var/www/html /var/www/html

# Expose Railway’s port
EXPOSE 8000

# Run migrations then serve app
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
