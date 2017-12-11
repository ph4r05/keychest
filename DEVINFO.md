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


#### Deadlock example - delete

```
_____________________________________________________________________________________________ Deadlock Transactions _____________________________________________________________________________________________
ID  Timestring           User      Host       Victim  Time   Undo  LStrcts  Query Text                                                                                                                           
 4  2017-12-09 09:54:02  keychest  localhost  Yes     00:00     0        3  select * from `jobs` where `queue` = ? and ((`reserved_at` is null and `available_at` <= ?) or (`reserved_at` <= ?)) order by `id` as
 5  2017-12-09 09:54:02  keychest  localhost  No      00:00     1        3  delete from `jobs` where `id` = ?                                                                                                    

______________________________________ Deadlock Locks _______________________________________
ID  Waiting  Mode  DB        Table  Index                         Special          Ins Intent
 4        1  X     keychest  jobs   PRIMARY                       rec but not gap           0
 5        0  X     keychest  jobs   PRIMARY                       rec but not gap           0
 5        1  X     keychest  jobs   jobs_queue_reserved_at_index  rec but not gap           0
```

- Solved by removing `lockForUpdate()` query, issuing delete by ID directly. 

#### Deadlock example - update

Deadlock:

```
______________________________________________________________________________________________________ Deadlock Transactions ______________________________________________________________________________________________________
ID    Timestring           User      Host       Victim  Time   Undo  LStrcts  Query Text                                                                                                                                           
8767  2017-12-11 18:08:03  keychest  localhost  No      00:00     1        5  update `jobs` set `reserved_at` = ?, `attempts` = ? where `id` = ?                                                                                   
8768  2017-12-11 18:08:03  keychest  localhost  Yes     00:00     0        4  select * from `jobs` where `queue` = ? and ((`reserved_at` is null and `available_at` <= ?) or (`reserved_at` <= ?)) order by `id` asc limit 1 for up

_______________________________________ Deadlock Locks ________________________________________
ID    Waiting  Mode  DB        Table  Index                         Special          Ins Intent
8767        0  X     keychest  jobs   PRIMARY                                                 0
8767        1  X     keychest  jobs   jobs_queue_reserved_at_index  gap before rec            1
8768        1  X     keychest  jobs   PRIMARY                       rec but not gap           0
```

Locks:

```
_____________________________________________________ InnoDB Locks _____________________________________________________
ID     Type    Waiting  Wait   Active    Mode  DB        Table  Index                         Ins Intent  Special       
 8767  RECORD        1  00:48  02:32:10  X     keychest  jobs   jobs_queue_reserved_at_index           0                
 8766  RECORD        1  00:30  02:27:51  X     keychest  jobs   jobs_queue_reserved_at_index           0                
10578  RECORD        1  00:22     00:22  X     keychest  jobs   jobs_queue_reserved_at_index           1  gap before rec
10579  RECORD        1  00:13     00:13  X     keychest  jobs   jobs_queue_reserved_at_index           1  gap before rec
```

Processes:

```
+-------+-----------+-----------------+----------+---------+------+----------------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+
| Id    | User      | Host            | db       | Command | Time | State          | Info                                                                                                                                                      |
+-------+-----------+-----------------+----------+---------+------+----------------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+
|  8766 | keychest  | localhost:40468 | keychest | Execute |    4 | Sorting result | select * from `jobs` where `queue` = ? and ((`reserved_at` is null and `available_at` <= ?) or (`reserved_at` <= ?)) order by `id` asc limit 1 for update |
|  8767 | keychest  | localhost:40472 | keychest | Execute |   22 | Sorting result | select * from `jobs` where `queue` = ? and ((`reserved_at` is null and `available_at` <= ?) or (`reserved_at` <= ?)) order by `id` asc limit 1 for update |
| 10581 | keychest  | localhost:36070 | keychest | Execute |   25 | update         | insert into `jobs` (`queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) values (?, ?, ?, ?, ?, ?)                                |
| 10582 | keychest  | localhost:36072 | keychest | Execute |   16 | update         | insert into `jobs` (`queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) values (?, ?, ?, ?, ?, ?)                                |
+-------+-----------+-----------------+----------+---------+------+----------------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+
```

Transactions:
```
+----------------+----------------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+-----------------+-----------------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+
| waiting_trx_id | waiting_thread | waiting_query                                                                                                                                             | blocking_trx_id | blocking_thread | blocking_query                                                                                                                                            |
+----------------+----------------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+-----------------+-----------------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+
| 62D36A9        |          10593 | insert into `jobs` (`queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) values (?, ?, ?, ?, ?, ?)                                | 62C97B1         |            8766 | select * from `jobs` where `queue` = ? and ((`reserved_at` is null and `available_at` <= ?) or (`reserved_at` <= ?)) order by `id` asc limit 1 for update |
| 62D36A9        |          10593 | insert into `jobs` (`queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) values (?, ?, ?, ?, ?, ?)                                | 62C9348         |            8767 | select * from `jobs` where `queue` = ? and ((`reserved_at` is null and `available_at` <= ?) or (`reserved_at` <= ?)) order by `id` asc limit 1 for update |
| 62D36A9        |          10593 | insert into `jobs` (`queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) values (?, ?, ?, ?, ?, ?)                                | 62C9345         |            8768 | NULL                                                                                                                                                      |
| 62D36A9        |          10593 | insert into `jobs` (`queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) values (?, ?, ?, ?, ?, ?)                                | 62C9345         |            8768 | NULL                                                                                                                                                      |
| 62D36A0        |          10592 | insert into `jobs` (`queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) values (?, ?, ?, ?, ?, ?)                                | 62C97B1         |            8766 | select * from `jobs` where `queue` = ? and ((`reserved_at` is null and `available_at` <= ?) or (`reserved_at` <= ?)) order by `id` asc limit 1 for update |
| 62D36A0        |          10592 | insert into `jobs` (`queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) values (?, ?, ?, ?, ?, ?)                                | 62C9348         |            8767 | select * from `jobs` where `queue` = ? and ((`reserved_at` is null and `available_at` <= ?) or (`reserved_at` <= ?)) order by `id` asc limit 1 for update |
| 62D36A0        |          10592 | insert into `jobs` (`queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) values (?, ?, ?, ?, ?, ?)                                | 62C9345         |            8768 | NULL                                                                                                                                                      |
| 62D36A0        |          10592 | insert into `jobs` (`queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) values (?, ?, ?, ?, ?, ?)                                | 62C9345         |            8768 | NULL                                                                                                                                                      |
| 62C97B1        |           8766 | select * from `jobs` where `queue` = ? and ((`reserved_at` is null and `available_at` <= ?) or (`reserved_at` <= ?)) order by `id` asc limit 1 for update | 62C9345         |            8768 | NULL                                                                                                                                                      |
| 62C9348        |           8767 | select * from `jobs` where `queue` = ? and ((`reserved_at` is null and `available_at` <= ?) or (`reserved_at` <= ?)) order by `id` asc limit 1 for update | 62C97B1         |            8766 | select * from `jobs` where `queue` = ? and ((`reserved_at` is null and `available_at` <= ?) or (`reserved_at` <= ?)) order by `id` asc limit 1 for update |
| 62C9348        |           8767 | select * from `jobs` where `queue` = ? and ((`reserved_at` is null and `available_at` <= ?) or (`reserved_at` <= ?)) order by `id` asc limit 1 for update | 62C9345         |            8768 | NULL                                                                                                                                                      |
+----------------+----------------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+-----------------+-----------------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+
11 rows in set (0.00 sec)
```

Lock info:

```
+---------------------+-------------+-----------+-----------+-------------------+--------------------------------+------------+-----------+----------+------------------------------+
| lock_id             | lock_trx_id | lock_mode | lock_type | lock_table        | lock_index                     | lock_space | lock_page | lock_rec | lock_data                    |
+---------------------+-------------+-----------+-----------+-------------------+--------------------------------+------------+-----------+----------+------------------------------+
| 62C97B1:0:52555:193 | 62C97B1     | X         | RECORD    | `keychest`.`jobs` | `jobs_queue_reserved_at_index` |          0 |     52555 |      193 | 'sshpool', 1513015683, 18744 |
| 62C9348:0:52555:193 | 62C9348     | X,GAP     | RECORD    | `keychest`.`jobs` | `jobs_queue_reserved_at_index` |          0 |     52555 |      193 | 'sshpool', 1513015683, 18744 |
| 62C9345:0:52555:193 | 62C9345     | X,GAP     | RECORD    | `keychest`.`jobs` | `jobs_queue_reserved_at_index` |          0 |     52555 |      193 | 'sshpool', 1513015683, 18744 |
| 62C9345:0:52555:193 | 62C9345     | X,GAP     | RECORD    | `keychest`.`jobs` | `jobs_queue_reserved_at_index` |          0 |     52555 |      193 | 'sshpool', 1513015683, 18744 |
| 62C97B1:0:52555:193 | 62C97B1     | X         | RECORD    | `keychest`.`jobs` | `jobs_queue_reserved_at_index` |          0 |     52555 |      193 | 'sshpool', 1513015683, 18744 |
| 62C9348:0:52555:193 | 62C9348     | X,GAP     | RECORD    | `keychest`.`jobs` | `jobs_queue_reserved_at_index` |          0 |     52555 |      193 | 'sshpool', 1513015683, 18744 |
| 62C9345:0:52555:193 | 62C9345     | X,GAP     | RECORD    | `keychest`.`jobs` | `jobs_queue_reserved_at_index` |          0 |     52555 |      193 | 'sshpool', 1513015683, 18744 |
| 62C9345:0:52555:193 | 62C9345     | X,GAP     | RECORD    | `keychest`.`jobs` | `jobs_queue_reserved_at_index` |          0 |     52555 |      193 | 'sshpool', 1513015683, 18744 |
| 62C9345:0:52555:193 | 62C9345     | X,GAP     | RECORD    | `keychest`.`jobs` | `jobs_queue_reserved_at_index` |          0 |     52555 |      193 | 'sshpool', 1513015683, 18744 |
| 62C97B1:0:52555:193 | 62C97B1     | X         | RECORD    | `keychest`.`jobs` | `jobs_queue_reserved_at_index` |          0 |     52555 |      193 | 'sshpool', 1513015683, 18744 |
| 62C9345:0:52555:193 | 62C9345     | X,GAP     | RECORD    | `keychest`.`jobs` | `jobs_queue_reserved_at_index` |          0 |     52555 |      193 | 'sshpool', 1513015683, 18744 |
+---------------------+-------------+-----------+-----------+-------------------+--------------------------------+------------+-----------+----------+------------------------------+
```

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

