#!/bin/bash

cd /var/www/keychest
sudo rsync -a tools/supervisor.d/*.conf /etc/supervisord.d/
sudo cp ~/keychest-scanner/assets/supervisord.d/keychest.conf /etc/supervisord.d/

# epiper helper
sudo cp /var/www/keychest/tools/epiper.sh /usr/bin/epiper
sudo chmod +x /usr/bin/epiper

# Edit supervisor.d config so it also takes .conf files into account
if ! grep -Fq "supervisord.d/*.conf" /etc/supervisord.conf
then
    read -r -d '' TO_ADD <<- EOM
        [include]
        files = supervisord.d/*.conf
    EOM
    echo $TO_ADD | sudo tee /etc/supervisord.conf
fi

# supervisor config reload & start
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status

# cron
echo '* * * * * nginx php /var/www/keychest/artisan schedule:run >> /dev/null 2>&1' | sudo tee /etc/cron.d/keychest


