# Supervisord
https://laravel.com/docs/5.4/queues#supervisor-configuration

## Install
`sudo apt-get install supervisor`

## Config
Config dir:

 - /etc/supervisor/conf.d
 - /etc/supervisor.d/
 
## Operation

```bash
sudo supervisorctl reread

sudo supervisorctl update

sudo supervisorctl start laravel-worker:*
```

## Update

Workers do not re-read PHP code updates, thus to reload code changes restart all workers.

```bash
php artisan queue:restart
```

After workers are killed supervisor will restart them all with new code.

