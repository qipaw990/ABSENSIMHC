# Dockerfile untuk Sistem Absensi QR — SMK MHC
# Base Image: PHP 8.2 FPM Alpine (ringan)
FROM php:8.2-fpm-alpine

# Install dependencies sistem
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    nginx \
    supervisor \
    mysql-client \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    icu-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        opcache

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files dahulu (cache layer)
COPY composer.json composer.lock ./

# Install PHP dependencies (tanpa dev)
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy semua source code
COPY . .

# Install & build assets frontend (jika ada vite)
RUN if [ -f "package.json" ]; then npm ci && npm run build && rm -rf node_modules; fi

# Set permission
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy konfigurasi Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf

# Copy konfigurasi PHP-FPM
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy konfigurasi Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy entrypoint script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Expose port 80
EXPOSE 80

# Jalankan supervisord
ENTRYPOINT ["/entrypoint.sh"]
