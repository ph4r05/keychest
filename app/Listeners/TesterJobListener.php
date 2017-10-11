<?php

namespace App\Listeners;

use App\Events\ScanJobProgress;
use App\Events\ScanJobProgressNotif;
use App\Events\TesterJobProgress;
use App\Events\TesterJobProgressNotif;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class TesterJobListener implements ShouldQueue
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
     * @param  TesterJobProgress  $event
     * @return void
     */
    public function handle($event)
    {
        if (!($event instanceof TesterJobProgress)){
            Log::debug('Invalid object received (expected TesterJobProgress):  '
                . var_export($event, true));
            return;
        }

        Log::info('New event: ' . var_export($event->getJsonData(), true));

        // TODO: email processing event - job for that.
        // TODO: scan results - rebroadcast to WS.
        // Rewrap and rebroadcast to the socket server
        $e = TesterJobProgressNotif::fromEvent($event);
        event($e);
    }
}
