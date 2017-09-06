#!/bin/bash

curl -sL https://rpm.nodesource.com/setup_8.x | sudo -E bash -
sudo yum remove -y nodejs npm
sudo yum install -y nodejs
sudo npm install -g npm

