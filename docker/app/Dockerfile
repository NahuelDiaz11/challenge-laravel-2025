FROM php:8.2-fpm-alpine

RUN apk update && apk add \
    postgresql-dev \
    libpq \
    libzip-dev \
    zip \
    pcre-dev \
    ${PHPIZE_DEPS} \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && pecl install redis \
    && docker-php-ext-enable redis

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
COPY . .

EXPOSE 9000
CMD ["php-fpm"]
