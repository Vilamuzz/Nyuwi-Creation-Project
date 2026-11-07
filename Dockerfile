# Stage 1: PHP deps
FROM php:8.2-cli-alpine AS vendor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN apk add --no-cache git unzip libpng-dev oniguruma-dev libxml2-dev \
 && docker-php-ext-install bcmath exif pcntl gd
WORKDIR /app
COPY composer.* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --no-dev

# Stage 2: build frontend
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# Stage 3: final
FROM php:8.2-fpm-alpine
RUN apk add --no-cache libpng-dev oniguruma-dev libxml2-dev \
 && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

WORKDIR /var/www
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=vendor /app/vendor ./vendor
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build

USER www-data
EXPOSE 9000
CMD ["php-fpm"]
