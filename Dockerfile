FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    mariadb-client-compat \
    nodejs \
    npm \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql gd bcmath zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/

COPY . .

RUN COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction --prefer-dist --optimize-autoloader
RUN npm install

RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]