FROM php:8.3-fpm

# Install PHP extensions en dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libonig-dev libxml2-dev \
    zip libzip-dev \
 && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Composer dependencies eerst (snellere builds bij kleine veranderingen)
# COPY laravel/composer.json laravel/composer.lock ./
COPY laravel/ ./
RUN composer install --no-dev --optimize-autoloader

# Rest van de app
# COPY laravel/ ./
RUN mkdir -p /var/www/storage \
    /var/www/storage/framework/views \
    /var/www/storage/framework/cache \
    /var/www/storage/framework/sessions \
    /var/www/bootstrap/cache
# Permissions fix
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache