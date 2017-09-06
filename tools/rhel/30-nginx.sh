#!/bin/bash

cd /var/www/keychest
sudo cp tools/nginx/conf.d/* /etc/nginx/conf.d/

# nginx config edit
export NGINX_HTTP_CFG=/etc/nginx/conf.d/keychest.conf
export NGINX_HTTPS_CFG=/etc/nginx/conf.d/keychest-tls.conf

# nginx domain names
sudo sed -i "s/server_name \(.\+\);\$/server_name \1 $KC_DOMAIN;/g" $NGINX_HTTP_CFG
sudo sed -i "s/server_name \(.\+\);\$/server_name \1 $KC_DOMAIN;/g" $NGINX_HTTPS_CFG

# nginx certificate
sudo sed -i "s#ssl_certificate \(.\+\);\$#ssl_certificate $KC_CERT_CHAIN#g" $NGINX_HTTPS_CFG
sudo sed -i "s#ssl_certificate_key \(.\+\);\$#ssl_certificate_key $KC_CERT_PRIV#g" $NGINX_HTTPS_CFG

# nginx hash bucket size
echo 'server_names_hash_bucket_size 128;' | sudo tee /etc/nginx/conf.d/01-nginx.conf

# edit config files - URL, certificates
sudo systemctl restart nginx.service

# in case of a problem add following line to the /etc/nginx/nginx.conf to the 'http' directive
# server_names_hash_bucket_size 128;

cd /var/www/keychest
sudo ./fix.sh

