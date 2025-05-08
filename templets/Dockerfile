# 1) Imagen base con Apache + PHP
FROM php:8.2-apache

# 2) Instala extensiones de MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 3) Habilita mod_rewrite si usas .htaccess
RUN a2enmod rewrite

# 4) Copia tu código al directorio público de Apache
COPY . /var/www/html/

# 5) Expone el puerto 80 (el interno de Apache)
EXPOSE 80

# 6) Arranca Apache en primer plano
CMD ["apache2-foreground"]
