<?php

namespace App\Console;

use App\Console\Commands\CheckCertificateValidityCommand;
use App\Console\Commands\MigrateGenerateCommand;
use App\Console\Commands\Setup;
use App\Console\Commands\SetupEcho;
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
        MigrateGenerateCommand::class,
        Setup::class,
        SetupEcho::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->command(CheckCertificateValidityCommand::class)  // ['--id=2']
             ->everyTenMinutes()
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
