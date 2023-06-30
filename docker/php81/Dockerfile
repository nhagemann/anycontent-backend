FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y git

RUN apt-get install -y zip unzip wget

RUN apt-get install -y --no-install-recommends libpng-dev libwebp-dev libfreetype6-dev libjpeg62-turbo-dev libicu-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp

RUN docker-php-ext-install pdo_mysql gd intl

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Symfony CLI
RUN wget https://get.symfony.com/cli/installer -O - | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Set working directory
WORKDIR /var/www

# Endless command to keep container running
CMD tail -f /dev/null