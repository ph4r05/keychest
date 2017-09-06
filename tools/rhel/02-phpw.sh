#!/bin/bash
# https://webtatic.com/packages/php56/
sudo yum install php56w-cli php56w-php-fpm \
 php56w-fpm php56w-mysqlnd php56w-mbstring php56w-gd php56w-xml \
 php56w-pecl-xdebug php56w-opcache php56w-intl \
 php56w-pear

# change apache to nginx
# /etc/php-fpm.d/www.conf
sudo sed -i 's/user = apache/user = nginx/g' /etc/php-fpm.d/www.conf
sudo sed -i 's/group = apache/group = nginx/g' /etc/php-fpm.d/www.conf

sudo systemctl enable php-fpm.service
sudo systemctl start php-fpm.service
sudo systemctl status php-fpm.service

# Composer
cd /tmp

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
sudo php composer-setup.php --install-dir=/bin --filename=composer


