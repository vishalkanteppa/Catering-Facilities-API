FROM php:8.0-apache

WORKDIR /var/www/html

RUN apt-get update && docker-php-ext-install pdo pdo_mysql

COPY . /var/www/html/

# enable Apache rewrite for simplicity
RUN a2enmod rewrite

EXPOSE 80

# start apache server
CMD ["apache2-foreground"]
