#!/bin/sh
source ~/.bashrc
set -e

echo "Deploying application ..."

# Migrate database
php artisan migrate --force

# Run seeders
# php artisan db:seed

# Set JWT-secret
php artisan jwt:secret

# Clear cache
php artisan optimize

echo "Application deployed!"
