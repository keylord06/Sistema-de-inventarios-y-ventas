#!/bin/sh
# ==========================================================================
#  Entrypoint de la capa de NEGOCIO
#  - Ajusta el puerto de Apache a $PORT (los PaaS lo asignan dinamicamente)
#  - Genera /var/www/html/.env a partir de variables de entorno
#    (asi los secretos no quedan dentro de la imagen ni en el repositorio)
# ==========================================================================
set -e

PORT="${PORT:-80}"
sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

# Permite pasar la llave por archivo (Docker secrets): KEY_FILE=/run/secrets/jwt_key
if [ -z "$KEY" ] && [ -n "$KEY_FILE" ] && [ -f "$KEY_FILE" ]; then
    KEY="$(tr -d '\r\n' < "$KEY_FILE")"
fi
KEY="$(printf '%s' "$KEY" | tr -d '\r\n')"
if [ -z "$KEY" ]; then
    KEY="$(head -c 48 /dev/urandom | base64 | tr -d '\n=+/')"
    echo "[negocio] AVISO: no se definio KEY; se genero una llave temporal." >&2
    echo "[negocio]        Los tokens dejaran de ser validos al reiniciar el contenedor." >&2
fi

ENV_FILE=/var/www/html/.env
{
    echo "KEY='${KEY}'"
    echo "API_DATA='${API_DATA:-http://webdatos/api}'"
    echo "API_TIMEOUT='${API_TIMEOUT:-5}'"
    [ -n "$CORS_ALLOWED_ORIGINS" ] && echo "CORS_ALLOWED_ORIGINS='${CORS_ALLOWED_ORIGINS}'"
    true
} > "$ENV_FILE"
chown www-data:www-data "$ENV_FILE"
chmod 640 "$ENV_FILE"

echo "[negocio] Escuchando en el puerto ${PORT}; capa de datos: ${API_DATA:-http://webdatos/api}"
exec docker-php-entrypoint "$@"
