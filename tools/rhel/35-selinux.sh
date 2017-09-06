/bin/bash

# check redis port reserved state:
sepolicy network -p 6379

sudo semanage port -a -t http_port_t -p tcp 6379
sudo semanage port -a -t http_port_t -p tcp 6001
sudo semanage port -a -t http_port_t -p tcp 9000
sudo setsebool -P httpd_can_network_connect_db 1

cd /var/www/keychest/tools/selinux
checkmodule -M -m -o httpd-to-redis-socket.mod httpd-to-redis-socket.te
semodule_package -o httpd-to-redis-socket.pp -m httpd-to-redis-socket.mod
sudo semodule -i httpd-to-redis-socket.pp
cd -

sudo chcon -Rt httpd_sys_content_t /var/www
sudo chcon -Rt httpd_sys_rw_content_t /var/www/keychest/storage/
sudo semanage fcontext -a -t httpd_sys_content_t "/var/www(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/keychest/storage(/.*)?"
restorecon -Rv /var/www
ls -lZ /var/www
