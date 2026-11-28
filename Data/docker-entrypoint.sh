#!/bin/sh
# ==========================================================================
#  Entrypoint de la capa de DATOS
#  - Ajusta el puerto de Apache a $PORT
#  - Lee la contrasena de BD desde DB_PASSW o desde DB_PASSWORD_FILE (secret)
#  - Genera /var/www/html/.env
#    (asi los secretos no quedan dentro de la imagen ni en el repositorio)
# ==========================================================================
set -e

PORT="${PORT:-80}"
sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

if [ -z "$DB_PASSW" ] && [ -n "$DB_PASSWORD_FILE" ] && [ -f "$DB_PASSWORD_FILE" ]; then
    DB_PASSW="$(tr -d '\r\n' < "$DB_PASSWORD_FILE")"
fi
DB_PASSW="$(printf '%s' "$DB_PASSW" | tr -d '\r\n')"

ENV_FILE=/var/www/html/.env
{
    echo "DB_HOST='${DB_HOST:-db}'"
    echo "DB_USER='${DB_USER:-app_user}'"
    echo "DB_PASSW='${DB_PASSW}'"
    echo "DB_NAME='${DB_NAME:-inventario_ventas}'"
    true
} > "$ENV_FILE"
chown www-data:www-data "$ENV_FILE"
chmod 640 "$ENV_FILE"

echo "[datos] Escuchando en el puerto ${PORT}; BD: ${DB_HOST:-db}/${DB_NAME:-inventario_ventas}"
exec docker-php-entrypoint "$@"
