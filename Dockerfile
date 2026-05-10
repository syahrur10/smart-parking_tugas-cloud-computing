FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY ./frontend /var/www/html
COPY ./backend /var/www/backend

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80