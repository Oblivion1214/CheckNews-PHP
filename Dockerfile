# 1) Imagen base con Apache + PHP
FROM php:8.2-apache

# 2) Instala dependencias del sistema y extensiones PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql

# 3) Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4) Habilita mod_rewrite
RUN a2enmod rewrite

# 5) Copia solo los archivos necesarios (mejor que copiar todo)
COPY . /var/www/html/

# 6) Si usas Composer, instala dependencias (descomenta si es necesario)
# WORKDIR /var/www/html
# RUN composer install --no-dev --optimize-autoloader

# 7) Ajusta permisos (importante para Laravel u otros frameworks)
# RUN chown -R www-data:www-data /var/www/html/storage
# RUN chown -R www-data:www/html/bootstrap/cache

# 8) Expone el puerto 80
EXPOSE 80

# 9) Arranca Apache en primer plano
CMD ["apache2-foreground"]