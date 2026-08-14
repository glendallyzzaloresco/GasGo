# FROM php:8.2-cli

FROM php:8.4-fpm


RUN apt-get update && apt-get install -y \
	git unzip curl libzip-dev zip libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install pdo pdo_mysql zip gd


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer




WORKDIR /var/www




COPY . .


# RUN composer install --no-dev --optimize-autoloader
RUN composer install --no-dev -o

# Render provides runtime env vars (APP_KEY/DB_*). Avoid baking a .env + key into the image.


EXPOSE 10000




CMD sh -c "php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"
