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
    echo $TO_ADD | sudo tee -a /etc/supervisord.conf
fi

# supervisor config reload & start
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status

# cron
echo '* * * * * nginx php /var/www/keychest/artisan schedule:run >> /dev/null 2>&1' | sudo tee /etc/cron.d/keychest

#### END

# Supervisor environment
# Supervisord runs services undes custom environment. In order to transfer env vars to the
# service you can add 'environment' config directive either main config file /etc/supervisord.conf
# under [supervisord] directive or in the particular service.
# example:
#
# environment=
#   NODE_EXTRA_CA_CERTS=%(ENV_NODE_EXTRA_CA_CERTS)s,
#   REQUESTS_CA_BUNDLE=%(ENV_REQUESTS_CA_BUNDLE)s,
#   KC_TRUST_ROOTS_AUX=%(ENV_KC_TRUST_ROOTS_AUX)s


# Supervisor running under systemd may have issues with environment variables set in /etc/environment
# If that is the case look up systemd unit file supervisord.service, usually
# /etc/systemd/system/multi-user.target.wants/supervisord.service
# And add to the [Service] section:
# EnvironmentFile=/etc/environment

# Then call
# sudo systemctl daemon-reload


