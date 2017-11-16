# Minor developer info / notes

Used components / endpoints configuration

## Horizon

https://laravel.com/docs/5.5/horizon

```
php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider"
```

## Admin LTE

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

## Social endpoints

* https://github.com/acacha/laravel-social
* https://developers.facebook.com/apps/
* https://console.developers.google.com
* https://apps.twitter.com/app/new
* https://www.linkedin.com/secure/developer

Facebook, Google and Linked in support more callback URIs,
for Github and Twitter there has to be a separate app for each 
new domain (test, dev, production).
