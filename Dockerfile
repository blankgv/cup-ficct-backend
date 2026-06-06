# Imagen del backend Laravel. PostgreSQL es externo.
FROM php:8.4-cli

# Dependencias del sistema.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype-dev \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP (PostgreSQL, GD para imágenes/Excel, etc.).
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        bcmath \
        intl \
        zip \
        opcache \
        gd

# Composer.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Instala dependencias primero (cache de capas).
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --prefer-dist --no-progress

# Copia el proyecto.
COPY . .

RUN composer dump-autoload --optimize

EXPOSE 8000

# Arranque: migra, siembra datos base (idempotente), genera docs y sirve en $PORT.
CMD php artisan migrate --force \
    && php artisan db:seed --class=AuthenticationSeeder --force \
    && php artisan db:seed --class=AcademicManagementSeeder --force \
    && php artisan l5-swagger:generate \
    && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
