# DB migrations for testing

Since PHP Laravel backend and KeyChest Master operate on the
same database only one DB versioning tool can be used actively.
 
The main database management tool is the KeyChest Master, namely Alembic.
This choice has been made because KC Master is the primary data processing tool
with data intensive operations with the database. I find Alembic very easy to use.
E.g., it supports generating new revisions (diff) from the running database.  
 
Laravel migrations are not used for a primary migration logic. The Laravel migrations 
are used only for models managed solely by the Laravel, i.e. unused by the KC Master
(e.g., social users table). No KC Master can depend on those and vice versa. Once
such dependency is made it has to be converted to Alembic.
The Alembic is the main manager. 

Example: `Users` is a Laravel model by origin but many other relations used by KC Master
depend on it (foreign keys) thus it is being managed by Alembic. Laravel Migrations are not 
used for `Users` table.

However for the PHP testing purposes we need a way to build in memory sqlite database with
complete model.  For this purpose a there is a migrations generator command that automatically 
generates PHP migrations from the DB into `migrations_tests` directory.   
 
These migration files are used to build in memory testing database if test cases uses the 
following trait:

```php
use GeneratedDatabaseMigrations;
```

## Generating migrations from DB

Before the testing generate fresh migration files from the DB.

```bash
/bin/rm database/migrations_tests/*.php
php artisan migrate:generateForTest -n --env=dev --path=database/migrations_tests/  
``` 

In the interactive mode you may get the question:

```
Do you want to log these migrations in the migrations table? [Y/n] :
 > n
```

In general we don't want to add these test migrations into the migrations table.

## Remote run

It is possible to run `ssh -L 3306:localhost:3306 keychestdev` and do the 
migration generation locally.


## Laravel generated tables

If there is a migration (e.g., generated by the thirdparty plugin) that contains a new table
not yet managed by Alembic it is recommended to:

- Run the migration
- Run the Alembic so it creates a new revision with new table added
- Disable php migration file
- Regenerate `migrations_tests` migrations.

   