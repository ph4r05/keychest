#!/bin/bash

# https://rpms.remirepo.net/enterprise/remi-release-6.rpm
# https://rpms.remirepo.net/enterprise/remi-release-7.rpm

sudo rpm -Uvh https://rpms.remirepo.net/enterprise/remi-release-6.rpm
sudo yum-config-manager --enable remi-php71

sudo yum localinstall -y http://vault.centos.org/6.5/SCL/x86_64/scl-utils/scl-utils-20120927-8.el6.centos.alt.x86_64.rpm

sudo yum install php71 php71-fpm php71-mysqlnd php71-mbstring php71-gd php71-xml \
    php71-pecl-xdebug php71-opcache php71-intl \
    php71-pear php71-pecl-redis

# change apache to nginx
# /etc/php-fpm-7.1.d/www.conf
sudo sed -i 's/user = apache/user = nginx/g' /etc/php-fpm-7.1.d/www.conf
sudo sed -i 's/group = apache/group = nginx/g' /etc/php-fpm-7.1.d/www.conf

sudo chkconfig php-fpm-7.1 --level=345 on
sudo /etc/init.d/php-fpm-7.1 start
sudo /etc/init.d/php-fpm-7.1 status

# Amazon linux is RHEL 6 based, so no systemcl
# sudo systemctl enable php71-fpm.service
# sudo systemctl start php71-fpm.service
# sudo systemctl status php71-fpm.service

# Composer
cd /tmp
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo php composer-setup.php --install-dir=/bin --filename=composer


