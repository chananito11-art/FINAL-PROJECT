#!/bin/bash
set -e

# Ensure required directories exist
mkdir -p /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views /var/www/html/bootstrap/cache /var/www/html/public/uploads

# Fix ownership and permissions so the web server can write logs and caches
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/uploads || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/uploads || true

# Execute the container command
exec "$@"
