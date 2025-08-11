FROM php:8.2-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    libgd-dev \
    librabbitmq-dev \
    libssl-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# Configuration et installation des extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache

# Installation de Redis
RUN pecl install redis && docker-php-ext-enable redis

# Installation de AMQP pour RabbitMQ
RUN pecl install amqp && docker-php-ext-enable amqp

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration des permissions
RUN chown -R www-data:www-data /var/www

# Configuration PHP-FPM
RUN echo "www-data ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Répertoire de travail
WORKDIR /var/www/html

# Utilisateur
USER www-data

# Exposition du port
EXPOSE 9000

# Commande par défaut
CMD ["php-fpm"]
