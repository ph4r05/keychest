#!/bin/bash

sudo npm install -g node-pre-gyp gyp

# Old version, not working anymore
# sudo npm install -g --unsafe-perm node-sqlite3

# If the previous installation fails, try this:
sudo npm install -g --unsafe-perm https://github.com/mapbox/node-sqlite3/tarball/master

# Install laravel echo server
sudo npm uninstall -g laravel-echo-server
sudo npm install -g --unsafe-perm laravel-echo-server
