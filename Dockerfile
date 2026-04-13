FROM php:8.4-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libicu-dev \
    libcurl4-openssl-dev \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath intl \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY vhost.conf /etc/apache2/sites-available/000-default.conf
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]