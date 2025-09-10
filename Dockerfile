FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql mysqli
ENV APACHE_DOCUMENT_ROOT=/var/www/public
RUN sed -ri -e 's!/var/www/html!/var/www/public!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
WORKDIR /var/www