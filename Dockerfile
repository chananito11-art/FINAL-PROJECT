FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    libpng-dev \
    libzip-dev

# Install Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
<<<<<<< HEAD
# Set working directory
WORKDIR /var/www/html
# Copy Laravel app
COPY . .
# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction
# Install frontend dependencies and build assets
RUN npm install && npm run build
RUN php artisan config:clear \
&& php artisan route:clear \
&& php artisan view:clear
# Create storage symlink
RUN php artisan storage:link || true
# Fix permissions
RUN mkdir -p storage/framework/cache storage/framework/sessions \
storage/framework/views bootstrap/cache public/uploads \
&& chown -R www-data:www-data storage bootstrap/cache public/uploads \
&& chmod -R 775 storage bootstrap/cache public/uploads
# (Optional) Run migrations
RUN php artisan migrate --force || true
# Expose port
EXPOSE 10000
# Start Apache
CMD ["apache2-foreground"]
=======

WORKDIR /var/www

COPY . .

RUN composer install

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000
>>>>>>> 3ad544b45b8112d991d224f4e7b4e80dc6b5fd6d
