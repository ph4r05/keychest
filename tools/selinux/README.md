# Selinux policy

```bash
cat httpd-redis-audit.log | audit2allow -M httpd-to-redis-socket
semodule -i httpd-to-redis-socket.pp
```

OR

```bash
checkmodule -M -m -o httpd-to-redis-socket.mod httpd-to-redis-socket.te
semodule_package -o httpd-to-redis-socket.pp -m httpd-to-redis-socket.mod
sudo semodule -i httpd-to-redis-socket.pp
```

