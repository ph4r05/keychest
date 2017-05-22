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
