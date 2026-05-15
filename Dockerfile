FROM php:8.2-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev \
        zip \
        unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

RUN printf '%s\n' \
    'file_uploads=On' \
    'upload_max_filesize=10M' \
    'post_max_size=12M' \
    'max_file_uploads=20' \
    'expose_php=Off' \
    > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
