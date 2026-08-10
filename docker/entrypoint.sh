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

# The database is a single SQLite file under storage/, which is wiped along
# with the rest of the container filesystem on every free-tier redeploy or
# restart - so re-create it fresh on every boot instead of expecting it to
# already exist. Idempotent either way (migrate.php's `up` only applies
# migrations not already recorded as run). Set DB_AUTO_SEED=false to skip
# this once the app has a persistent disk and real data to keep.
if [ "${DB_AUTO_SEED:-true}" = "true" ]; then
    su -s /bin/sh www-data -c "php /var/www/html/database/migrate.php fresh --seed" || true
fi

exec "$@"
