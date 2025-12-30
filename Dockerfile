FROM php:8.1-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libldap2-dev \
    ghostscript \
    git \
    unzip \
    curl \
    cron \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    mysqli \
    pdo_mysql \
    zip \
    intl \
    soap \
    opcache \
    exif \
    ldap \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configurar Apache
RUN a2enmod rewrite expires headers

# Descargar e instalar Moodle
WORKDIR /var/www/html
RUN git clone --branch MOODLE_404_STABLE --depth 1 https://github.com/moodle/moodle.git . \
    && mkdir /var/www/moodledata \
    && chown -R www-data:www-data /var/www/html /var/www/moodledata \
    && chmod -R 755 /var/www/moodledata

# Copiar el plugin de Feedback con IA
COPY plugin/ /var/www/html/mod/assign/feedback/ai/

# Instalar dependencias del plugin con Composer
WORKDIR /var/www/html/mod/assign/feedback/ai
RUN composer install --no-dev --optimize-autoloader || true

# Volver al directorio de Moodle
WORKDIR /var/www/html

# Ajustar permisos del plugin
RUN chown -R www-data:www-data /var/www/html/mod/assign/feedback/ai

# Configuración PHP
RUN echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_input_vars = 5000" >> /usr/local/etc/php/conf.d/uploads.ini

# Configurar cron para Moodle (cada minuto)
RUN echo "* * * * * www-data /usr/local/bin/php /var/www/html/admin/cli/cron.php > /dev/null 2>&1" > /etc/cron.d/moodle-cron \
    && chmod 0644 /etc/cron.d/moodle-cron

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear script de inicialización
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
