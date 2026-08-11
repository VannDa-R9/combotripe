#!/bin/bash
set -e

# Run migrations (preserves existing data, only applies new schema changes)
php artisan migrate --force

# Clear and rebuild cache
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache
