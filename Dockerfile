FROM php:7.1.33-fpm

# Copy composer.lock and composer.json
COPY /home/ubuntu/fleetmastr-2.30.0/package-lock.json /home/ubuntu/fleetmastr-2.30.0/composer.json /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Install dependencies
# RUN apt-get update && \
 #   apt-get install -y build-essential locales git unzip zip curl 

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install nvm
#RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.38.0/install.sh | bash
#ENV NVM_DIR=/root/.nvm
#RUN . $NVM_DIR/nvm.sh && nvm install 7.8.0 && nvm use 7.8.0

# Set default node version
#RUN . $NVM_DIR/nvm.sh && nvm alias default 7.8.0
#CMD [ "node" ]

# Install npm 4.2.0
#RUN npm install -g npm@4.2.0

# Install gulp-cli
# RUN npm install -g gulp-cli

# Install extensions
# RUN docker-php-ext-install pdo_mysql mbstring  exif pcntl
RUN apt-get update && apt-get install -y libonig-dev

# Set environment variables
ENV PKG_CONFIG_PATH=/usr/local/lib/pkgconfig
ENV ONIG_CFLAGS=-I/usr/local/include
ENV ONIG_LIBS=-L/usr/local/lib

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory contents
COPY . /var/www/html

# Copy existing application directory permissions
COPY --chown=www:www . /var/www/html

# Change current user to www
USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]

