<?php

namespace App\Listeners;

use App\Events\ScanJobProgress;
use App\Events\ScanJobProgressNotif;
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
     * Create the event listener.
     *
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
    public function handle($event)
    {
        if (!($event instanceof ScanJobProgress)){
            Log::debug('Invalid object received (expected ScanJobProgress): '
                . var_export($event, true));
            return;
        }

        Log::info('New event: ' . var_export($event->getJsonData(), true));

        // Rewrap and rebroadcast to the socket server
        $e = ScanJobProgressNotif::fromEvent($event);
        event($e);
    }
}
