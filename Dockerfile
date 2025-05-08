# Imagen base con Apache + PHP 8.2
FROM php:8.2-apache

# Copiar archivos del proyecto al directorio público de Apache
COPY . /var/www/html/

# Habilitar mod_rewrite si usas .htaccess
RUN a2enmod rewrite
