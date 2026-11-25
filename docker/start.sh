#!/bin/sh
set -e
php artisan storage:link || true
php artisan config:clear
php artisan migrate --force
# Cargar datos de ejemplo solo la primera vez: poner RUN_SEED=true en Render y quitarla después
if [ "$RUN_SEED" = "true" ]; then
  php artisan db:seed --class=SaludCercaSeeder --force
fi
exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
