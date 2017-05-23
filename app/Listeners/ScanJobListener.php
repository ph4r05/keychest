<?php

namespace App\Listeners;

use App\Events\ScanJobProgress;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ScanJobListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the connection the job should be sent to.
     *
     * @var string|null
     */
    public $connection = 'ph4redis';

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    //public $queue = null;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ScanJobProgress  $event
     * @return void
     */
    public function handle(ScanJobProgress $event)
    {
        Log::info('New event: ' . var_export($event, true));
    }
}
