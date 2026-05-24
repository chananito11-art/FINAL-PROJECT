FROM php:8.2-apache

# Use the Laravel public folder as Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf
RUN a2enmod rewrite
RUN printf '%s\n' '<Directory /var/www/html/public>' '    Options Indexes FollowSymLinks' '    AllowOverride All' '    Require all granted' '</Directory>' > /etc/apache2/conf-available/laravel.conf
RUN a2enconf laravel

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    libpng-dev \
    libzip-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy app and install dependencies
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction || true

# Install frontend dependencies and build assets (if package.json exists)
RUN if [ -f package.json ]; then npm install && npm run build; fi || true

# Clear caches and create storage symlink
RUN php artisan config:clear && php artisan route:clear && php artisan view:clear || true
RUN php artisan storage:link || true

# Fix permissions for storage and cache so Apache can write logs and cached files
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache public/uploads \
    && chown -R www-data:www-data storage bootstrap/cache public/uploads \
    && chmod -R 775 storage bootstrap/cache public/uploads || true

# (Optional) Run migrations if DB is available at build time
RUN php artisan migrate --force || true

EXPOSE 80

# Copy and enable entrypoint script to fix permissions at container start
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
