# -----------------------------------
# Stage 1: Base PHP Image
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
# Stage 2: Install dependencies
# -----------------------------------
FROM base AS build

# Copy only composer files first (for caching)
COPY composer.json composer.lock ./

# Now that gd extension exists, Composer can safely install deps
RUN composer install --no-dev --optimize-autoloader --no-interaction || composer install --ignore-platform-reqs

# Copy the rest of the app files
COPY . .

# -----------------------------------
# Stage 3: Production Image
# -----------------------------------
FROM base AS production

WORKDIR /var/www/html

# Copy vendor and app from build stage
COPY --from=build /var/www/html /var/www/html

# Clear and optimize Laravel caches
RUN php artisan config:clear && php artisan route:clear && php artisan view:clear
RUN php artisan optimize

# Expose port (Railway uses $PORT automatically)
EXPOSE 8000

# Run migrations then serve app
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
