#!/bin/sh
source ~/.bashrc
set -e

echo "Deploying application ..."

# Caminho completo para o executável do Composer
/home2/luizan96/api.luizantonio.dev.br/composer install --no-interaction --prefer-dist --optimize-autoloader

# Migrate database
php /home2/luizan96/api.luizantonio.dev.br/artisan migrate --force

# Run seeders
php /home2/luizan96/api.luizantonio.dev.br/artisan db:seed

# Clear cache
php /home2/luizan96/api.luizantonio.dev.br/artisan optimize

echo "Application deployed!"
