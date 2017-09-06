#!/bin/bash

cd

# Release download - use git instead
# wget https://github.com/EnigmaBridge/keychest-scanner/archive/v${KC_SCANNER_VER}.tar.gz
# tar -xzvf v${KC_SCANNER_VER}.tar.gz
# cd keychest-scanner-${KC_SCANNER_VER}

# Git clone / tag fetch
git clone https://github.com/EnigmaBridge/keychest-scanner.git keychest-scanner
cd keychest-scanner
git checkout tags/v${KC_SCANNER_VER} -b rel_v${KC_SCANNER_VER}

sudo /usr/local/bin/pip install -U --find-links=. .

