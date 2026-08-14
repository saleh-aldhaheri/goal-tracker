FROM php:8.3-fpm AS base

RUN apt-get update && apt-get install -y \
    git curl unzip libsqlite3-dev libzip-dev libonig-dev libxml2-dev \
    libicu-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    nodejs npm \
    && docker-php-ext-install pdo pdo_sqlite zip mbstring xml bcmath intl \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY package.json package-lock.json ./
RUN npm install

COPY . .

RUN composer dump-autoload --optimize \
    && npm run build

RUN mkdir -p database storage/framework/{sessions,views,cache} storage/logs \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 storage database

EXPOSE 8080

# database/ is expected to be a persistent volume mount in production
# (see docs/AGENT_CONTEXT.md) — database.sqlite must survive restarts.
# InitialAccountSeeder is safe to run on every deploy: it's fully
# idempotent (updateOrCreate throughout) and never fabricates progress,
# so redeploys just re-sync goal/topic definitions, not activity history.
CMD touch database/database.sqlite \
    && php artisan migrate --force --no-interaction \
    && php artisan db:seed --class=InitialAccountSeeder --force --no-interaction \
    && php artisan config:cache \
    && php artisan serve --host=0.0.0.0 --port=8080
