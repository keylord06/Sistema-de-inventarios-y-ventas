FROM php:8.5-apache
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends git unzip zip \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite actions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Composer desde la imagen oficial (evita el hash del instalador que cambia con cada versión)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY ./Data/composer.json .
RUN composer install --no-interaction --no-dev --prefer-dist

COPY ./Data/.env .
COPY ./Data/src ./src
COPY ./Data/public ./public
RUN composer dump-autoload --optimize

RUN sed -i 's#DocumentRoot.*$#DocumentRoot /var/www/html/public#' /etc/apache2/sites-enabled/000-default.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
       > /etc/apache2/conf-enabled/app.conf
