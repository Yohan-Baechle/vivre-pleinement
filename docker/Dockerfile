# syntax=docker/dockerfile:1

# ════════════════════════════════════════════════════════════
# Image de production — Nginx + PHP-FPM 8.5 + Composer + Node
# ════════════════════════════════════════════════════════════
FROM php:8.5-fpm-bookworm AS runtime

# Dépendances système + extensions PHP requises par Laravel/Filament/MediaLibrary
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        mariadb-client \
        git \
        unzip \
        curl \
        ca-certificates \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libwebp-dev \
        libzip-dev \
        libonig-dev \
        libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install gd \
    && docker-php-ext-install zip \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install exif \
    && docker-php-ext-install intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Node 22 (pour builder les assets Vite, qui dépendent du thème Filament dans vendor/)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dépendances PHP (couche cache : manifestes d'abord)
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction

# Dépendances Node (couche cache)
COPY package.json package-lock.json ./
RUN npm ci

# Code applicatif complet (vendor + node_modules déjà en place)
COPY . .

# Autoload PHP optimisé + build des assets (vendor/ présent → thème Filament OK)
RUN composer dump-autoload --optimize --no-dev \
    && npm run build \
    && rm -rf node_modules

# Configuration des services
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# Droits sur les dossiers écrits à l'exécution
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
