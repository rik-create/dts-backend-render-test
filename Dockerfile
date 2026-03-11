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

# Install dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Generate optimized autoload files
RUN composer dump-autoload --optimize

# Generate JWT keys right in the container
RUN mkdir -p storage/keys
RUN openssl genrsa -out storage/keys/private.key 2048
RUN openssl rsa -in storage/keys/private.key -pubout -out storage/keys/public.key
RUN openssl genrsa -out storage/keys/oauth-private.key 2048
RUN openssl rsa -in storage/keys/oauth-private.key -pubout -out storage/keys/oauth-public.key
RUN chmod -R 777 storage/keys

# Set permissions for Laravel
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Serve
CMD sh -c "php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"
