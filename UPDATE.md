# Updating 

- KW = KeyChest Web
- KS = KeyChest Scanner


```bash
#
# SHUTTING DOWN
#

# 1. KW enable maintenance mode 
php artisan down

# 2. KS stop web workers
./tools/supervisor-stop.sh 

# 3. KS stop scanner 
epiper supervisorctl stop keychest

#
# UPDATING
#

# 4. KS update - sync & install
# git pull / rsync & pip install

# 5. KS update database - run migrations
alembic upgrade head

# 6. KW new dependencies
composer install 

# 7. KW database migrations
php artisan migrate:status
php artisan migrate

# 8. KW clean reinstall of NPM packages
/bin/rm -rf package-lock.json yarn.lock node_modules
npm install

# 9. KW rebuild js/css
npm run prod

#
# STARTING
#

# 10. KW start
epiper supervisorctl start keychest

# 11. KS web workers start  
./tools/supervisor-start.sh

# 12. KW disable maintenance mode
php artisan up
```

 

