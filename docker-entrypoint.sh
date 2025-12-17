#!/bin/bash
set -e

# Esperar a que MariaDB esté lista
echo "Esperando a que MariaDB esté lista..."
max_tries=30
counter=0
until php -r "new PDO('mysql:host=$MOODLE_DATABASE_HOST', '$MOODLE_DATABASE_USER', '$MOODLE_DATABASE_PASSWORD');" 2>/dev/null || [ $counter -eq $max_tries ]; do
    echo "Esperando MariaDB... (intento $counter/$max_tries)"
    sleep 2
    counter=$((counter + 1))
done

if [ $counter -eq $max_tries ]; then
    echo "ERROR: No se pudo conectar a MariaDB"
    exit 1
fi

echo "MariaDB está lista!"

# Asegurar permisos correctos
chown -R www-data:www-data /var/www/moodledata
chmod -R 0777 /var/www/moodledata

echo "Moodle listo para instalación web en http://localhost:8080/install.php"

# Asegurar permisos correctos
chown -R www-data:www-data /var/www/html /var/www/moodledata
chmod -R 755 /var/www/html/mod/assign/feedback/ai 2>/dev/null || true

echo "Iniciando Apache..."
exec "$@"
