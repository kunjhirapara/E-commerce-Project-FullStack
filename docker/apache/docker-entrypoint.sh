#!/bin/sh
set -eu

# Ensure runtime-mounted upload directories are writable by Apache.
UPLOAD_DIRS="
/var/www/html/assets/images/uploads
/var/www/html/assets/images/user-sign-up-uploads
/var/www/html/admin/assets/images/admin-sign-up-uploads
"

for dir in $UPLOAD_DIRS; do
    mkdir -p "$dir"
    chown -R www-data:www-data "$dir"
    chmod -R 775 "$dir"
done

APP_BASE_PATH="${APP_BASE_PATH:-}"
APP_BASE_PATH="$(printf '%s' "$APP_BASE_PATH" | sed 's#/*$##')"

if [ -n "$APP_BASE_PATH" ] && [ "${APP_BASE_PATH#/}" = "$APP_BASE_PATH" ]; then
    APP_BASE_PATH="/$APP_BASE_PATH"
fi

ROUTE_RULES='    RewriteEngine On
    RewriteRule ^admin/?$ /admin/signin.php [R=302,L]'

if [ -n "$APP_BASE_PATH" ]; then
    BASE_PATTERN="${APP_BASE_PATH#/}"
    ROUTE_RULES="    RewriteEngine On
    RewriteRule ^${BASE_PATTERN}/admin/?$ ${APP_BASE_PATH}/admin/signin.php [R=302,L]
    RewriteRule ^${BASE_PATTERN}/?$ / [PT,L]
    RewriteRule ^${BASE_PATTERN}/(.*)$ /\$1 [PT,L]
    RewriteRule ^admin/?$ ${APP_BASE_PATH}/admin/signin.php [R=302,L]"
fi

cat > /etc/apache2/sites-available/000-default.conf <<EOF
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    DirectoryIndex index.php index.html index.htm

    <Directory /var/www/html>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

${ROUTE_RULES}

    <LocationMatch "^/\\.">
        Require all denied
    </LocationMatch>

    <Files "conn.php">
        Require all denied
    </Files>

    <Directory /var/www/html/assets/images/uploads>
        <FilesMatch "\\.(php|php5|phtml|phar|sh|pl|py|cgi)$">
            Require all denied
        </FilesMatch>
    </Directory>

    <Directory /var/www/html/assets/images/user-sign-up-uploads>
        <FilesMatch "\\.(php|php5|phtml|phar|sh|pl|py|cgi)$">
            Require all denied
        </FilesMatch>
    </Directory>

    <Directory /var/www/html/admin/assets/images/admin-sign-up-uploads>
        <FilesMatch "\\.(php|php5|phtml|phar|sh|pl|py|cgi)$">
            Require all denied
        </FilesMatch>
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

exec apache2-foreground
