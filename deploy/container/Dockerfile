FROM php:8.2.29-apache-bookworm@sha256:c2408ddfc8988020e521f5a666cf23e11cba795f03fc1dbc4b1c233337ca7d5f

RUN apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . /var/www/html
COPY deploy/container/apache-affiliate.conf /etc/apache2/conf-available/affiliate-offers.conf
COPY deploy/container/entrypoint.sh /usr/local/bin/affiliate-offers-entrypoint

RUN a2enconf affiliate-offers \
    && chmod 0755 /usr/local/bin/affiliate-offers-entrypoint \
    && mkdir -p storage/logs storage/imports storage/backups \
    && chown -R www-data:www-data storage \
    && rm -rf deploy .git .env

ENTRYPOINT ["affiliate-offers-entrypoint"]
CMD ["apache2-foreground"]
