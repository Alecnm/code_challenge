FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
        unzip \
        git \
        curl \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN cp .env.example .env && php artisan key:generate --force

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
