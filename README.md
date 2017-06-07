# Key Chest

Certificate monitor

## Development - setup

Edit `.env` file.

```bash
mkdir -p storage/logs/
chmod +w storage/logs/

composer install
npm install
php artisan migrate --force
```

### Resource compilation

```bash
npm run dev
npm run prod
npm run watch-poll
```

On Vue component modification re-upload compiled resources in `public/`

Do not edit CSS / JS files in `public/` directly, its generated. Changes will be lost.

### Operation

Queue management - obsolete now processed by python component.

```bash
php artisan queue:work redis --queue=scanner --sleep 0.05
```

Queued events processing

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

