#!/bin/bash

sudo yum install php71 php71-php php71-fpm \
 php71-fpm php71-mysqlnd php71-mbstring php71-gd php71-xml \
 php71-pecl-xdebug php71-opcache php71-intl \
 php71-pear php71-pecl-redis

# change apache to nginx
# /etc/php-fpm-7.1.d/www.conf
sudo sed -i 's/user = apache/user = nginx/g' /etc/php-fpm-7.1.d/www.conf
sudo sed -i 's/group = apache/group = nginx/g' /etc/php-fpm-7.1.d/www.conf

sudo systemctl enable php71-fpm.service
sudo systemctl start php71-fpm.service
sudo systemctl status php71-fpm.service

# Composer
cd /tmp
source /etc/profile.d/php.sh

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
sudo /opt/rh/rh-php56/root/bin/php composer-setup.php --install-dir=/bin --filename=composer


