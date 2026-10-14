#!/bin/sh
set -eu

port="${PORT:-10000}"
case "$port" in
    ''|*[!0-9]*) echo "PORT must be a numeric TCP port." >&2; exit 1 ;;
esac
if [ "$port" -lt 1 ] || [ "$port" -gt 65535 ]; then
    echo "PORT is outside the valid TCP port range." >&2
    exit 1
fi

sed -i "s/^Listen 80$/Listen ${port}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf
mkdir -p /var/www/html/storage/framework/sessions /var/www/html/storage/framework/rate-limits
chown -R www-data:www-data /var/www/html/storage/framework
exec "$@"