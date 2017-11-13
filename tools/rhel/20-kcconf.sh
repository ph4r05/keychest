#!/bin/bash

# Scanner config setup, DB setup

cd ~/keychest-scanner
sudo /usr/local/bin/keychest-setup --root-pass "${KC_MYSQL_ROOT_PASSWD}" --init-db --init-alembic

# KeyChest setup
cd /var/www/keychest
sudo php artisan app:setup --prod --db-config-auto && sudo ./fix.sh
php artisan app:setupEcho --init-prod
php artisan key:generate
php artisan dotenv:set-key APP_URL https://${KC_DOMAIN}
php artisan down

# Scanner Database setup, phase 2
cd ~/keychest-scanner
sudo -E -H /usr/local/bin/pip install alembic
alembic upgrade head

# Laravel DB Migration
cd -
php artisan migrate
php artisan migrate:status

cd -

