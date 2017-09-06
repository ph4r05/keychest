#!/bin/bash

export KC_SCANNER_VER=0.1.8
export KC_VER=0.0.12
export KC_USER=ec2-user
export KC_DOMAIN=keychest.net
export KC_CERT_BASE=/etc/letsencrypt/live/${KC_DOMAIN}
export KC_CERT_CHAIN=/etc/letsencrypt/live/${KC_DOMAIN}/fullchain.pem
export KC_CERT_PRIV=/etc/letsencrypt/live/${KC_DOMAIN}/privkey.pem
export KC_MYSQL_ROOT_PASSWD=`head /dev/urandom | tr -dc A-Za-z0-9 | head -c 16`

echo "MySQL root password: ${KC_MYSQL_ROOT_PASSWD}" | tee ~/mysql-pass.txt

