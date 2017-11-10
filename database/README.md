# DB migrations for testing

Due to mixed DB management we don't have all table migrations stored
in the Laravel project. 

The primary database management system is Alembic 
used by the Keychest Master (Scanner, python).

Migrations for tests are generated from the DB.

## Generating migrations from DB

Before the testing generate fresh migration files from the DB.

```bash
php artisan migrate:generate --env=dev --path=database/migrations_tests/ --templatePath=database/MigrationTestTemplate.txt 

Do you want to log these migrations in the migrations table? [Y/n] :
 > n
``` 

## Remote run

It is possible to run `ssh -L 3306:localhost:3306 keychestdev` and do the 
migration generation locally.


 

