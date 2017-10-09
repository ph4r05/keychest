#!/bin/bash
# Installs Py2.7.14 and Py3.6.3

cd /tmp
wget https://www.python.org/ftp/python/2.7.14/Python-2.7.14.tgz
tar xzf Python-2.7.14.tgz
cd Python-2.7.14
./configure --enable-optimizations
sudo make altinstall

cd /tmp
wget https://www.python.org/ftp/python/3.6.3/Python-3.6.3.tgz
tar xzf Python-3.6.3.tgz
cd Python-3.6.3
./configure --enable-optimizations
sudo make altinstall

echo 'export PATH=/usr/local/bin:$PATH' | sudo tee /etc/profile.d/py-alt.sh
source /etc/profile.d/py-alt.sh

cd /tmp
curl "https://bootstrap.pypa.io/get-pip.py" -o "get-pip.py"
sudo /usr/local/bin/python2.7 get-pip.py
sudo /usr/local/bin/python3.6 get-pip.py

# Sudo path inclusion
ln -s /usr/local/bin/python2.7 /bin/python
ln -s /usr/local/bin/python2.7 /bin/python2
ln -s /usr/local/bin/python2.7 /bin/python2.7
ln -s /usr/local/bin/python3.6 /bin/python2.7
ln -s /usr/local/bin/python3.6 /bin/python3
ln -s /usr/local/bin/python3.6 /bin/python3.6
ln -s /usr/local/bin/python3.6m /bin/python3.6m
ln -s /usr/local/bin/pip /bin/python3.6
ln -s /usr/local/bin/pip /bin/python3.6
ln -s /usr/local/bin/pip2 /bin/pip
ln -s /usr/local/bin/pip2 /bin/pip2
ln -s /usr/local/bin/pip3.6 /bin/pip3.6

# In case of a problem use pip --cert /etc/pki/ca-trust/source/anchors/bcoatpip.pym
# to workaround problem with corporate proxy, add your certs to the verify paths
python -c "import ssl; print(ssl.get_default_verify_paths())"

# Make sure you setup CAs as described in node-corp.sh
export REQUESTS_CA_BUNDLE=/etc/pki/tls/cert.pem
echo 'REQUESTS_CA_BUNDLE=/etc/pki/tls/cert.pem' >> /etc/environment

# If that does not help:
# find: requests/cacert.pem
# find: cacerts.txt

