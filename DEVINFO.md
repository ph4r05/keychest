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


## InnoDB transactions

```bash
yum install innotop
innotop -uroot -ppassword
```
https://www.xaprb.com/blog/2006/07/31/how-to-analyze-innodb-mysql-locks/

### Database driver deadlocks

With normal DB job backend there was a following transaction deadlock scenario:

3 transactions:

 1. `delete from jobs where id=?`, holds PRIMARY, waits jobs_queue_reserved_at_idx
 2. `select next job`, waits PRIMARY
 3. `insert into jobs`, probably holds jobs_queue_reserved_at_idx


### InnoDB transaction status

InnoDB transaction status:

```sql
SHOW ENGINE INNODB STATUS \G
```

```sql
use keychest;
SELECT
  r.trx_id waiting_trx_id,
  r.trx_mysql_thread_id waiting_thread,
  r.trx_query waiting_query,
  b.trx_id blocking_trx_id,
  b.trx_mysql_thread_id blocking_thread,
  b.trx_query blocking_query
FROM       information_schema.innodb_lock_waits w
INNER JOIN information_schema.innodb_trx b
  ON b.trx_id = w.blocking_trx_id
INNER JOIN information_schema.innodb_trx r
  ON r.trx_id = w.requesting_trx_id;
```

```sql
USE INFORMATION_SCHEMA
SELECT * FROM INNODB_LOCK_WAITS;
```

```sql
USE INFORMATION_SCHEMA
SELECT INNODB_LOCKS.* 
FROM INNODB_LOCKS
JOIN INNODB_LOCK_WAITS
  ON (INNODB_LOCKS.LOCK_TRX_ID = INNODB_LOCK_WAITS.BLOCKING_TRX_ID);
```

