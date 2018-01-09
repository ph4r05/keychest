
## Regenerate database and run tests

Generating migration tables from existing DB layout.

DB layout managed by Alembic in the scanner part. KC part reconstructs migrations from the DB 
so it can use DB in tests.

```bash
/bin/rm database/migrations_tests/*.php
php artisan migrate:generateForTest -n --env=dev --path=database/migrations_tests/

./vendor/bin/phpunit
```

