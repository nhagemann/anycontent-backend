FROM php:7.2-apache

RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip \
    && a2enmod rewrite

RUN cd /; curl --silent --show-error https://getcomposer.org/installer | php