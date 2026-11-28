FROM php:8.5-apache
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends git unzip zip \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite actions

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY ./Business/composer.json .
RUN composer install --no-interaction --no-dev --prefer-dist

COPY ./Business/src ./src
COPY ./Business/public ./public
RUN composer dump-autoload --optimize

RUN sed -i 's#DocumentRoot.*$#DocumentRoot /var/www/html/public#' /etc/apache2/sites-enabled/000-default.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
       > /etc/apache2/conf-enabled/app.conf

# El .env NO se copia: se genera al arrancar a partir de variables de entorno.
COPY ./Business/docker-entrypoint.sh /usr/local/bin/app-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/app-entrypoint && chmod +x /usr/local/bin/app-entrypoint

ENV PORT=80
EXPOSE 80

ENTRYPOINT ["app-entrypoint"]
CMD ["apache2-foreground"]
