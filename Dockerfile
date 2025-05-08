# Usar la imagen oficial de PHP con Apache
FROM php:8.1-apache

# Habilitar mod_rewrite (si es necesario)
RUN a2enmod rewrite

# Instalar extensiones necesarias (como MySQL y sesiones)
RUN docker-php-ext-install mysqli

# Copiar los archivos de tu proyecto al contenedor
COPY . /var/www/html/

# Configurar las variables de entorno en el contenedor (opcional si se usa Docker Compose)
ENV DB_HOST=mysql.railway.internal
ENV DB_USER=root
ENV DB_PASSWORD=MwPvMsPHvPbBPOOBYdhKSVgVMUPndinp
ENV DB_NAME=railway
ENV DB_PORT=3306

# Exponer el puerto 80 (puerto predeterminado para HTTP)
EXPOSE 80

# Comando para iniciar Apache en primer plano
CMD ["apache2-foreground"]
