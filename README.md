# Key Chest

Certificate monitor

## Important

.env file is important - needed to be added as not versioned

1. never edit /public files
2. just sync /resource files, should be OK for changes in the "look"
3. then you need to rebuild CSS/JS - "npm run dev" in the project root - more info below

## Installation

* Install composer: https://getcomposer.org/download/
* Install NodeJS v6 + NPM

```
cd keychest/

composer install
npm istall
```

## Development - setup

The `.env` file contains current configuration for the environment Keychest is deployed on. 
`.env` file must not be commited to the Git.

```bash
mkdir -p storage/logs/
chmod +w storage/logs/

composer install
npm install
php artisan migrate --force
```

Recommended approach - have more `.env` files on the server, like
```
.env.dev
.env.test
.env.prod
```

Then symlink the `.env` file so it points to the file it corresponds to, e.g.,
for the development deployment:

```
ln -s .env.dev .env
```

### Resource compilation

Do not ever edit CSS/JS in `/public`, it is automatically generated
from resources. 

To recompile all resources call

```bash
# on dev
npm run dev

# on production (minification)
npm run prod

# dev with watching file changes
npm run watch-poll
```

On Vue component modification re-upload compiled resources in `public/`

Do not edit CSS / JS files in `public/` directly, its generated. Changes will be lost.

### Operation

Queue management - obsolete now processed by python component.

```bash
php artisan queue:work redis --queue=scanner --sleep 0.05
```

Queued events processing - processing events from web / python worker.

```bash
php artisan queue:work --sleep 0.05
```

### Mac Setup

```
echo 'export PATH=${PATH}:~/.composer/vendor/bin' >> ~/.bashrc

# install GNU sed
brew install gnu-sed --with-default-names

# place GNU sed on the path before using admin LTE cmds.
export PATH=/usr/local/bin/:$PATH
```

### EC2 dep

Install NodeJS

```bash
curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.32.0/install.sh | bash
. ~/.nvm/nvm.sh
nvm install 6
```

### Redis install

* <https://redis.io/topics/quickstart>
* <https://medium.com/@andrewcbass/install-redis-v3-2-on-aws-ec2-instance-93259d40a3ce>

```bash
wget http://download.redis.io/redis-stable.tar.gz
tar xvzf redis-stable.tar.gz
cd redis-stable
make

sudo mkdir /etc/redis
sudo mkdir /var/redis

sudo cp utils/redis_init_script /etc/init.d/redis_6379
sudo cp redis.conf /etc/redis/6379.conf
sudo mkdir /var/redis/6379

# Set daemonize to yes (by default it is set to no).
# Set the pidfile to /var/run/redis_6379.pid (modify the port if needed).
# Change the port accordingly. In our example it is not needed as the default port is already 6379.
# Set your preferred loglevel.
# Set the logfile to /var/log/redis_6379.log
# Set the dir to /var/redis/6379 (very important step!)

sudo /etc/init.d/redis_6379 start
```

Alternative init script:

```
sudo wget https://raw.githubusercontent.com/saxenap/install-redis-amazon-linux-centos/master/redis-server

sudo mv redis-server /etc/init.d
sudo chmod 755 /etc/init.d/redis-server

sudo vi /etc/init.d/redis-server
# REDIS_CONF_FILE="/etc/redis/6379.conf" 

sudo chkconfig --add redis-server
sudo chkconfig --level 345 redis-server on
sudo service redis-server start
```


### Admin LTE

https://github.com/acacha/adminlte-laravel

```
composer global require "acacha/adminlte-laravel-installer=~3.0"

adminlte-laravel install

llum boot
```

Social OAuths:

```
adminlte-laravel social
```

### Social endpoints

* https://github.com/acacha/laravel-social
* https://developers.facebook.com/apps/
* https://console.developers.google.com
* https://apps.twitter.com/app/new
* https://www.linkedin.com/secure/developer

Facebook, Google and Linked in support more callback URIs,
for Github and Twitter there has to be a separate app for each 
new domain (test, dev, production).
