#!/bin/sh
source ~/.bashrc
set -e

echo "Deploying application ..."

# Install dependencies based on lock file
# composer install --no-interaction --prefer-dist --optimize-autoloader

# Migrate database
php artisan migrate --force

# Run seeders
php artisan db:seed

# Clear cache
php artisan optimize

echo "Application deployed!"
