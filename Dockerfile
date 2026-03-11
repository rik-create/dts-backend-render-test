FROM php:8.2-apache

# Install basic dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel
RUN docker-php-ext-install pdo_mysql mbstring pcntl bcmath gd

# Set the working directory
WORKDIR /var/www/html

# Replace standard Apache document root to point to Laravel's public directory
RUN sed -i -e "s|/var/www/html|/var/www/html/public|g" /etc/apache2/sites-available/000-default.conf
RUN sed -i -e "s|/var/www/|/var/www/html/public|g" /etc/apache2/apache2.conf

# Enable Apache mod_rewrite for Laravel routing
RUN a2enmod rewrite

# Copy composer from the official composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the rest of the application code
COPY . .

# Set permissions for Laravel storage and bootstrap/cache (Apache runs as www-data)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Install dependencies (ignoring dev dependencies for smaller size and security)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Generate optimized autoload files
RUN composer dump-autoload --optimize

# Disable any conflicting MPM modules and firmly enable prefork
RUN a2dismod mpm_event mpm_worker || true
RUN a2enmod mpm_prefork

# Set ports dynamically at runtime and start Apache
CMD sh -c "php artisan config:cache && php artisan route:cache && php artisan view:cache && sed -i \"s/Listen 80/Listen ${PORT:-80}/g\" /etc/apache2/ports.conf && sed -i \"s/:80/:${PORT:-80}/g\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"
