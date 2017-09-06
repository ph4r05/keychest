#!/bin/bash

sudo yum update rh-amazon-rhui-client.noarch
sudo yum-config-manager --enable rhui-REGION-rhel-server-rhscl
sudo yum install rh-php56 rh-php56-php rh-php56-php-fpm \
 rh-php56-php-fpm rh-php56-php-mysqlnd rh-php56-php-mbstring rh-php56-php-gd rh-php56-php-xml \
 rh-php56-php-pecl-xdebug rh-php56-php-opcache rh-php56-php-intl \
 rh-php56-php-pear

# change apache to nginx
# /etc/opt/rh/rh-php56/php-fpm.d/www.conf
sudo sed -i 's/user = apache/user = nginx/g' /etc/opt/rh/rh-php56/php-fpm.d/www.conf
sudo sed -i 's/group = apache/group = nginx/g' /etc/opt/rh/rh-php56/php-fpm.d/www.conf

sudo systemctl enable rh-php56-php-fpm.service
sudo systemctl start rh-php56-php-fpm.service
sudo systemctl status rh-php56-php-fpm.service

echo 'export PATH=$PATH:/opt/rh/rh-php56/root/bin' | sudo tee /etc/profile.d/php.sh
sudo ln -s /opt/rh/rh-php56/root/bin/php /bin/php

# Composer
cd /tmp
source /etc/profile.d/php.sh

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
sudo /opt/rh/rh-php56/root/bin/php composer-setup.php --install-dir=/bin --filename=composer


