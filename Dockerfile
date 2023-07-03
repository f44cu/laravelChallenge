# Use the official PHP base image
FROM php:8.0-fpm

# Set the working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the application files to the container
COPY . /var/www/html

# Install application dependencies
RUN composer install --no-interaction --no-dev --prefer-dist

# Generate the application key
RUN php artisan key:generate

# Set the permissions for storage and bootstrap directories
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# Copy the Nginx configuration file
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Expose port 80 for the web server
EXPOSE 80

# Start the PHP-FPM process
CMD ["php-fpm"]
