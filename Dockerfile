# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
&& apt-get install -y nodejs
# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
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
