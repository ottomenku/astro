#!/usr/bin/env bash
set -euo pipefail

cd /var/www/astro

echo "==> Permissions"
sudo mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache

echo "==> Migrations"
sudo -u www-data php artisan migrate --force

echo "==> Frontend build"
npm ci
npm run build

echo "==> Laravel cache"
sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan optimize

echo "==> Apache reload"
sudo systemctl reload apache2

echo "DEPLOY_OK"
