#!/bin/sh
source ~/.bashrc
set -e

echo "Deploying application ..."

# Install dependencies based on lock file
/opt/cpanel/composer/bin/composer install --no-interaction --prefer-dist --optimize-autoloader

# Migrate database
php artisan migrate --force

# Run seeders
php artisan db:seed

# Set JWT-secret
php artisan jwt:secret

# Clear cache
php artisan optimize

echo "Application deployed!"
