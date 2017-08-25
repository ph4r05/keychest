# Installation

Prepare `.env` file, set database credentials, fill-in e-mail settings.

## Installation

* Install composer: https://getcomposer.org/download/
* Install NodeJS v6 + NPM

```
cd keychest/

composer install
npm istall
```

## Configuration

The `.env` file contains current configuration for the environment Keychest is deployed on. 
`.env` file must not be commited to the Git.

```bash
mkdir -p storage/logs/
chmod +w storage/logs/
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

## Dependencies

```bash
composer install
npm install
php artisan migrate
```

## Task scheduler

Add to crontab: 

```
* * * * * nginx php /var/www/keychest/artisan schedule:run >> /dev/null 2>&1
```

## Supervisor.d - workers

Copy all files from `tools/supervisor.d/` to `/etc/supervisor.d`

```bash
sudo epiper supervisorctl reread
sudo epiper supervisorctl update
```

For more info please refer to the [tools/supervisor.md]

[tools/supervisor.md]: https://github.com/EnigmaBridge/keychest/blob/master/tools/supervisor.md

## Build resources

Do not ever edit CSS/JS in `/public`, it is automatically generated
from resources. 

To recompile all resources call

```bash
# on dev
nice -n 19 npm run dev

# on production (minification, versioning)
nice -n 19 npm run prod

# dev with watching file changes
nice -n 19 npm run watch-poll
```

On Vue component modification re-upload compiled resources in `public/`

Do not edit CSS / JS files in `public/` directly, its generated. Changes will be lost.


## Websocket server

Make user-based node installation, if you don't have that yet

```bash
#
# As root
#

rsync -av /root/.nvm/versions/node/v6.10.3/ /opt/node-6.10.3/
chown -R ec2-user /opt/node-6.10.3
npm config set prefix /opt/node-6.10.3/
ln -s /opt/node-6.10.3/ /opt/node
echo 'export PATH=/opt/node-6.10.3/bin:$PATH' > /etc/profile.d/node.sh

#
# As ec2-user
#
source /etc/profile.d/node.sh 
sudo chown -R $(whoami) $(npm config get prefix)/{lib/node_modules,bin,share}
sudo chown -R $(whoami) $(npm config get prefix)
```

Install the required packages

```bash
npm install -g node-sqlite3

# If the previous installation fails, try this:
npm install -g https://github.com/mapbox/node-sqlite3/tarball/master

# Install laravel echo server
npm uninstall -g laravel-echo-server
npm install -g node-pre-gyp gyp laravel-echo-server
```

More info: [Laravel Echo Server]

[Laravel Echo Server]: https://github.com/tlaverdure/laravel-echo-server

Initial configuration, usually not needed as it is done by us:

```bash
laravel-echo-server init
```

Edit the example configuration, add new client with API key:

```bash
cp laravel-echo-server-prod.example.json laravel-echo-server-prod.json
```

Link the configuration

```bash
ln -s laravel-echo-server-prod.json laravel-echo-server.json
```


Starting the server (debug)

```bash
laravel-echo-server start
```

Or let _supervisord_ manage it.


## Configure social plugin - OAuth login

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


## Misc installation notes

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

Init script change to support `chkconfig`:

```
# chkconfig: - 65 37
# description:  Redis server
# processname: redis-server
# config: /etc/redis/6379.conf
# pidfile: /var/run/redis_6379.pid
```
