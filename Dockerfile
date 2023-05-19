FROM php:7.1 as app

RUN apt-get update -y
RUN apt-get install -y unzip libpq-dev libcurl4-gnutls-dev
RUN docker-php-ext-install pdo pdo_mysql bcmath

WORKDIR /var/www
COPY . .

COPY --from=composer:2.3.5 /usr/bin/composer /usr/bin/composer

ENV PORT=8000
ENTRYPOINT [ "entrypoint.sh" ]

# ==============================================================================
#  node
FROM node:7.8.0-alpine as node

WORKDIR /var/www
COPY . .

RUN npm install --global cross-env
RUN npm install
RUN chown -R node /var/www/node_modules

VOLUME /var/www/node_modules

