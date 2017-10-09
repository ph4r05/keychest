#!/bin/bash
sudo yum install -y wget
wget https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
sudo yum install -y epel-release-latest-7.noarch.rpm

wget https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
sudo yum install -y webtatic-release.rpm

sudo yum install -y gcc gcc-c++ make automake autoreconf libtool
sudo yum install -y git rsync vim htop wget mlocate screen tcpdump
sudo yum install -y python python-pip python-devel mysql-devel redhat-rpm-config gcc libxml2 \
    libxml2-devel libxslt libxslt-devel openssl-devel sqlite-devel libpng-devel \
    policycoreutils-devel setools-console readline-devel libzip-devel bzip2-devel libffi-devel

sudo yum install -y mariadb-server
sudo yum install -y --enablerepo=epel nginx
sudo yum install -y --enablerepo=epel redis
sudo yum install -y --enablerepo=epel supervisor
sudo yum install -y --enablerepo=epel nodejs
sudo yum install -y --enablerepo=epel python-pip python-setuptools python-wheel
sudo yum install -y --enablerepo=epel nasm

sudo systemctl enable mariadb.service
sudo systemctl enable nginx.service
sudo systemctl enable redis.service
sudo systemctl enable supervisord.service

sudo systemctl start mariadb.service
sudo systemctl start nginx.service
sudo systemctl start redis.service
sudo systemctl start supervisord.service


