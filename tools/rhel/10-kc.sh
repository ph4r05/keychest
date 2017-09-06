#!/bin/bash

sudo mkdir -p /var/www
cd /var/www
sudo mkdir keychest
sudo chown nginx:nginx keychest

# AWS only
# sudo usermod -a -G nginx ${KC_USER}

cd /tmp

# Release download - use git instead
# wget https://github.com/EnigmaBridge/keychest/archive/v${KC_VER}.tar.gz
# tar xvf v${KC_VER}.tar.gz
# sudo rsync -av keychest-${KC_VER}/ /var/www/keychest/

# Git clone / tag fetch
git clone https://github.com/EnigmaBridge/keychest keychest
cd keychest
git checkout tags/v${KC_VER} -b rel_v${KC_VER}
sudo rsync -av keychest/ /var/www/keychest/

cd /var/www/keychest
composer install
npm install
npm run prod

# git repository needed for npm run prod (git tag in the generated files)
# In case of release install (no git clone) create a new one with initial commit.

# MySQL migration fix:
# /var/www/keychest/vendor/acacha/laravel-social/database/migrations/2014_10_12_400000_create_social_users_table.php
# substitute json() with text()
sed -i 's/->json/->text/g' /var/www/keychest/vendor/acacha/laravel-social/database/migrations/2014_10_12_400000_create_social_users_table.php

