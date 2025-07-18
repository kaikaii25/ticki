# Use official PHP image with Apache
FROM php:8.1-apache

# Install system dependencies and PHP extensions
RUN apt-get update \
    && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zip unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Set recommended permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Copy custom php.ini if exists
COPY config/php.ini /usr/local/etc/php/conf.d/custom.ini

# Set environment variables for cloud (example, override in deployment)
ENV DB_HOST=localhost \
    DB_USER=root \
    DB_PASS= \
    DB_NAME=nissan_tickets \
    UPLOAD_PATH=uploads/

# Start Apache in the foreground
CMD ["apache2-foreground"] 