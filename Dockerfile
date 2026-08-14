FROM php:8.2-fpm AS base

RUN apt-get update && apt-get install -y \
    git curl unzip libsqlite3-dev libzip-dev nodejs npm nginx \
    && docker-php-ext-install pdo pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY package.json package-lock.json ./
RUN npm install

COPY . .

RUN composer dump-autoload --optimize \
    && npm run build

RUN mkdir -p database storage/framework/{sessions,views,cache} storage/logs \
    && touch database/database.sqlite \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 storage database

EXPOSE 8080

CMD php artisan migrate --force --no-interaction \
    && php artisan config:cache \
    && php artisan serve --host=0.0.0.0 --port=8080
