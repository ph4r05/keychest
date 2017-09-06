#!/bin/bash

cd /tmp
wget https://www.python.org/ftp/python/2.7.13/Python-2.7.13.tgz
tar xzf Python-2.7.13.tgz
cd Python-2.7.13
./configure --enable-optimizations
sudo make altinstall

echo 'export PATH=/usr/local/bin:$PATH' | sudo tee /etc/profile.d/py2.7.13.sh
source /etc/profile.d/py2.7.13.sh

cd /tmp
curl "https://bootstrap.pypa.io/get-pip.py" -o "get-pip.py"
sudo /usr/local/bin/python2.7 get-pip.py

# In case of a problem use pip --cert /etc/pki/ca-trust/source/anchors/bcoatpip.pym
# to workaround problem with corporate proxy, add your certs to the verify paths
python -c "import ssl; print(ssl.get_default_verify_paths())"

# Make sure you setup CAs as described in node-corp.sh
export REQUESTS_CA_BUNDLE=/etc/pki/tls/cert.pem
echo 'REQUESTS_CA_BUNDLE=/etc/pki/tls/cert.pem' >> /etc/environment

# If that does not help:
# find: requests/cacert.pem
# find: cacerts.txt

