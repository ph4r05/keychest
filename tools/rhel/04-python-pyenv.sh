#!/bin/bash
# Disadvantage - only 2 or 3 can be activated at a time
# Disadvantage - sudo python -V does not work easily. ln -s does not work well.
# EXPERIMENTAL

sudo git clone https://github.com/pyenv/pyenv.git /opt/pyenv
sudo chown $KC_USER /opt/pyenv

echo 'export PYENV_ROOT="/opt/pyenv"' >> ~/.bashrc
echo 'export PATH="$PYENV_ROOT/bin:$PATH"' >> ~/.bashrc
echo 'eval "$(pyenv init -)"' >> ~/.bashrc
exec $SHELL

pyenv install 2.7.14
pyenv install 3.6.3
pyenv local 2.7.14

echo 'export PYENV_ROOT="/opt/pyenv"' | sudo tee /etc/profile.d/pyenv.sh
echo 'export PATH=/opt/pyenv/shims:/opt/pyenv/bin:$PATH' | sudo tee -a /etc/profile.d/pyenv.sh
source /etc/profile.d/pyenv.sh

# In case of a problem use pip --cert /etc/pki/ca-trust/source/anchors/bcoatpip.pym
# to workaround problem with corporate proxy, add your certs to the verify paths
python -c "import ssl; print(ssl.get_default_verify_paths())"

# Make sure you setup CAs as described in node-corp.sh
export REQUESTS_CA_BUNDLE=/etc/pki/tls/cert.pem
echo 'REQUESTS_CA_BUNDLE=/etc/pki/tls/cert.pem' >> /etc/environment

# If that does not help:
# find: requests/cacert.pem
# find: cacerts.txt

