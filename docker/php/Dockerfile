FROM php:8.3-fpm-bookworm

# Fast prebuilt extensions (avoids compiling intl/redis from source for ~5–10 min).
ADD --chmod=0755 \
    https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    /usr/local/bin/install-php-extensions

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip zip curl \
    && install-php-extensions pdo_mysql bcmath intl opcache zip redis \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN chmod +x /usr/local/bin/app-entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["app-entrypoint.sh"]
CMD ["php-fpm"]
