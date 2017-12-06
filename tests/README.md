
## Regenerate database and run tests

```bash
/bin/rm database/migrations_tests/*.php
php artisan migrate:generateForTest -n --env=dev --path=database/migrations_tests/

./vendor/bin/phpunit
```

