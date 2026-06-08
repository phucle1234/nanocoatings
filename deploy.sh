#!/bin/bash
set -euo pipefail

APP_DIR="/home/phamgroup/web/data.casumina.org/public_html/casuminaCRM"
BRANCH="main"

cd "$APP_DIR"

echo "==> Pull code"
git pull origin "$BRANCH"

echo "==> Install dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Laravel migrate & cache"
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true

php artisan storage:link

echo "==> Fix permissions"
chmod -R 775 storage bootstrap/cache

echo "==> DONE"