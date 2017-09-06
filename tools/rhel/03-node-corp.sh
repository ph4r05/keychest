#!/bin/bash

curl -sL https://rpm.nodesource.com/setup_8.x | sudo -E bash -
sudo yum remove -y nodejs npm
sudo yum install -y nodejs
sudo npm install -g npm

# In the corporate network there may be something like BlueCoat (proxy)
# Add corporate proxy CA to the trusted roots

# Remove g to include just the first cert
ex +'g/BEGIN CERTIFICATE/,/END CERTIFICATE/p' <(echo | openssl s_client -showcerts -connect github.com:443) -scq | \
    sudo tee -a /etc/pki/ca-trust/source/anchors/bcoat.pem

sudo update-ca-trust force-enable
sudo update-ca-trust extract

# TLS cert is used by python
cat /etc/pki/ca-trust/source/anchors/bcoat.pem >> /etc/pki/tls/cert.pem

# Node 7.4+ takes this ENV var - specify trust anchors there
echo 'NODE_EXTRA_CA_CERTS=/etc/pki/ca-trust/source/anchors/bcoat.pem' >> /etc/environment

### END

# Manual steps
# openssl s_client -showcerts -connect github.com:443 < /dev/null 2>&1 > bcoat.txt
# sudo cp bcoat.txt /etc/pki/ca-trust/source/anchors/bcoat.pem

# UNSECURE
# npm config set registry="http://registry.npmjs.org/"
# npm config set strict-ssl false

