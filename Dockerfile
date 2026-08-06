FROM php:8.2-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        libonig-dev \
        libssl-dev \
        pkg-config \
    && docker-php-ext-install -j"$(nproc)" mbstring pdo_mysql \
    && pecl install mongodb-2.3.3 \
    && docker-php-ext-enable mongodb \
    && a2enmod headers rewrite \
    && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS pkg-config \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

COPY . .

RUN cp config/database.example.php config/database.php \
    && cp config/mongodb.example.php config/mongodb.php \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
