FROM php:8.2-apache

# Install PDO and MySQL extensions for PHP
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache rewrite module if needed
RUN a2enmod rewrite

# Copy all project files into Apache's web root directory
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80