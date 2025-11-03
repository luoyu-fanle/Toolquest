# Gebruik officiële PHP 8.3 + Apache image
FROM php:8.3-apache

# Werkdir instellen in container
WORKDIR /var/www/html

# Nodige PHP-extensies installeren
RUN apt-get update && apt-get install -y \
    libpq-dev libzip-dev unzip git \
    && docker-php-ext-install pdo pdo_mysql zip

# Composer toevoegen
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Laravel bestanden kopiëren
COPY . .

# Dependencies installeren
RUN composer install --no-interaction --no-progress

# Apache configureren voor Laravel (public map als root)
RUN a2enmod rewrite
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Zorg dat Laravel schrijfrechten heeft
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Poort 80 openzetten
EXPOSE 80

# Apache starten
CMD ["apache2-foreground"]
