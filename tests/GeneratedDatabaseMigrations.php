<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 10.11.17
 * Time: 15:55
 */

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

trait GeneratedDatabaseMigrations
{
    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function runGeneratedDatabaseMigrations()
    {
        $this->artisan('migrate:fresh', ['--path' => 'database/migrations_tests']);

        $this->app[Kernel::class]->setArtisan(null);

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');

            RefreshDatabaseState::$migrated = false;
        });
    }
}