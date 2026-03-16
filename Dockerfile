FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev \
        zip \
        unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

RUN {
            echo 'file_uploads=On';
            echo 'upload_max_filesize=10M';
            echo 'post_max_size=12M';
            echo 'max_file_uploads=20';
        } > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html
COPY . /var/www/html
COPY docker/apache/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chown -R www-data:www-data \
    /var/www/html/assets/images/uploads \
    /var/www/html/assets/images/user-sign-up-uploads \
    /var/www/html/admin/assets/images/admin-sign-up-uploads \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
