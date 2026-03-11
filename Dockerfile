FROM php:8.2-cli

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

# Copy composer from the official composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the rest of the application code
COPY . .

# Install dependencies (ignoring dev dependencies for smaller size and security)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Generate optimized autoload files
RUN composer dump-autoload --optimize

# Set permissions for Laravel (since CLI might run as root, we just ensure it is readable/writable)
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Railway passes a dynamic PORT variable. We will use Laravel's built in server to completely bypass Apache crashes.
CMD sh -c "php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"
