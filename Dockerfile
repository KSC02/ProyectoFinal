FROM php:8.2-apache

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Habilitar mod_rewrite si deseas usarlo después
RUN a2enmod rewrite

# Copiar el código fuente al contenedor
COPY ./ /var/www/html/

# Establecer permisos correctos
RUN chown -R www-data:www-data /var/www/html