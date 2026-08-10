#!/bin/sh
set -e

# Render injects PORT at runtime (defaults to 10000); Apache's default config
# is hardcoded to port 80, so rewrite it on every container start.
PORT="${PORT:-10000}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Ensure storage/ exists and is writable even if it was wiped by a fresh
# container filesystem (Render's free tier has no persistent disk).
mkdir -p /var/www/html/storage/logs /var/www/html/storage/cache /var/www/html/storage/backups /var/www/html/storage/uploads/products
chown -R www-data:www-data /var/www/html/storage

exec "$@"
