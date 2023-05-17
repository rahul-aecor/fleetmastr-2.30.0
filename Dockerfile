#FROM php:7.1.0-fpm
FROM php:7.1.0-apache
WORKDIR /var/www/html

# Mod Rewrite
RUN a2enmod rewrite

RUN sed -i '/jessie-updates/d' /etc/apt/sources.list

# Linux Library
RUN apt-get update  && \
    apt-get install -y \
        libicu-dev \
        libmariadb-dev \
        unzip \
        zip \
        zlib1g-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev
    

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# PHP Extension
RUN docker-php-ext-install gettext intl pdo_mysql gd

RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd
    
    
    
    
# Copy application files
COPY . /var/www/html

# Set file permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Apache configuration
RUN a2enmod rewrite

# Start Apache
CMD ["apache2-foreground"]


