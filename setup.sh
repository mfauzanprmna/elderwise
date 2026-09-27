#!/usr/bin/env bash
set -e
APP_DIR="${1:-elderwise}"
composer create-project laravel/laravel "$APP_DIR" "^12.0"
cd "$APP_DIR"
cp -R ../elderwise-laravel12/app ./app
cp -R ../elderwise-laravel12/bootstrap ./bootstrap
cp -R ../elderwise-laravel12/database/migrations ./database/
cp -R ../elderwise-laravel12/database/seeders ./database/
cp -R ../elderwise-laravel12/resources/views ./resources/
cp -R ../elderwise-laravel12/public/css ./public/
cp -R ../elderwise-laravel12/public/js ./public/
cp ../elderwise-laravel12/routes/web.php ./routes/web.php
cp ../elderwise-laravel12/routes/console.php ./routes/console.php
cp ../elderwise-laravel12/.env.example ./.env
mkdir -p database
[ -f database/database.sqlite ] || touch database/database.sqlite
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
