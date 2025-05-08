# Usa una imagen oficial con Apache + PHP
FROM php:8.2-apache

# Habilita el módulo de reescritura si usas .htaccess (opcional)
RUN a2enmod rewrite

# Copia los archivos de tu proyecto al directorio público de Apache
COPY . /var/www/html/

# Establece permisos (opcional, si usas uploads o sesiones)
RUN chown -R www-data:www-data /var/www/html

# Expone el puerto 80 (Apache)
EXPOSE 80
