#!/bin/sh
set -e

# Named volumes get re-attached to these mount points on every start.
# The volume root is owned by root by default, so the www-data worker
# can't write to it. Fix ownership before php-fpm starts.
for dir in \
    /var/www/html/assets/images/uploads \
    /var/www/html/assets/images/user-sign-up-uploads \
    /var/www/html/admin/assets/images/admin-sign-up-uploads
do
    mkdir -p "$dir"
    chown www-data:www-data "$dir"
    chmod 755 "$dir"
done

# Hand off to the official php-fpm entrypoint
exec docker-php-entrypoint "$@"
