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
