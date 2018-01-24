<?php

namespace App\Console;

use App\Console\Commands\CheckCertificateValidityCommand;
use App\Console\Commands\CheckSshKeyPoolCommand;
use App\Console\Commands\DumpSshKeyCommand;
use App\Console\Commands\MigrateGenerateCommand;
use App\Console\Commands\Setup;
use App\Console\Commands\SetupEcho;
use App\Console\Commands\WorkCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CheckCertificateValidityCommand::class,
        CheckSshKeyPoolCommand::class,
        DumpSshKeyCommand::class,
        MigrateGenerateCommand::class,
        Setup::class,
        SetupEcho::class,
        WorkCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->command(CheckCertificateValidityCommand::class)
             ->everyTenMinutes()
             ->withoutOverlapping();

         $schedule->command(CheckSshKeyPoolCommand::class, ['--max-keys=20'])
             ->everyMinute()
             ->withoutOverlapping();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
