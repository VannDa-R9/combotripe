#!/bin/bash
set -e

# Run migrations
php artisan migrate --force

# Run seeders (safe with firstOrCreate)
php artisan db:seed --force

# Clear and rebuild cache
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
