# Stage 1: Composer dependencies
FROM php:8.4-cli AS composer
WORKDIR /app

# Install system dependencies and PHP extensions needed by Laravel
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    && docker-php-ext-install zip mbstring bcmath intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Stage 2: Runtime
FROM php:8.4-apache
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath intl \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY vhost.conf /etc/apache2/sites-available/000-default.conf
COPY --from=composer /app/vendor ./vendor
COPY . .

RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER www-data
EXPOSE 80

CMD ["apache2-foreground"]