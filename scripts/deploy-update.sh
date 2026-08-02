#!/usr/bin/env bash
set -euo pipefail

cd /var/www/astro

echo "==> Git pull"
git pull

echo "==> Permissions"
sudo mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache

echo "==> Drop stale bootstrap caches"
sudo rm -f bootstrap/cache/routes-v7.php
sudo rm -f bootstrap/cache/config.php
sudo rm -f bootstrap/cache/packages.php
sudo rm -f bootstrap/cache/services.php

echo "==> PHP dependencies"
composer install --no-dev --optimize-autoloader --no-scripts
sudo -u www-data php artisan package:discover --ansi

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
