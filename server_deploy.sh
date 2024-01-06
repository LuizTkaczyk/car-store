#!/bin/sh
set -e

echo "Deploying application ..."

# Update codebase
git fetch origin main
git reset --hard origin/main

# Install dependencies based on lock file
composer install --no-interaction --prefer-dist --optimize-autoloader

# Migrate database
php artisan migrate --force

# Clear cache
php artisan optimize

echo "Application deployed!"
