# 1) Imagen base con Apache + PHP
FROM php:8.2-apache

# 2) Instala extensiones de MySQL (si las necesitas)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 3) Habilita mod_rewrite (si usas .htaccess)
RUN a2enmod rewrite

# 4) Copia tu código al directorio público de Apache
COPY . /var/www/html/

# 5) Ajusta Apache para escuchar en el puerto $PORT
#    - ports.conf define Listen 80
#    - default-ssl.conf y 000-default.conf usan :80  
# Note: Railway inyecta la variable de entorno PORT en el contenedor
RUN sed -ri "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf \
&& sed -ri "s/:80/:${PORT}/g" /etc/apache2/sites-enabled/*.conf

# 6) Expone el puerto dinámico (solo informativo)
EXPOSE ${PORT}

# 7) Arranca Apache en primer plano
CMD ["apache2-foreground"]
