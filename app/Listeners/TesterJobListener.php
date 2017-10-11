<?php

namespace App\Listeners;

use App\Events\ScanJobProgress;
use App\Events\ScanJobProgressNotif;
use App\Events\TesterJobProgress;
use App\Events\TesterJobProgressNotif;
use App\Jobs\SendKeyCheckReport;
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

        try {
            $jobData = $event->getData();
            $jobType = isset($jobData->jobType) ? $jobData->jobType : null;
            Log::info('New event: ' . var_export($jobData, true));

            if ($jobType == 'email') {
                // Start the new user job
                $job = new SendKeyCheckReport($jobData);
                $job->onConnection(config('keychest.wrk_key_check_emails_conn'))
                    ->onQueue(config('keychest.wrk_key_check_emails_queue'));

                dispatch($job);
                return;
            }

            // Scan results - rebroadcast to WS.
            // Rewrap and rebroadcast to the socket server
            $e = TesterJobProgressNotif::fromEvent($event);
            event($e);

        } catch(\Exception $e){
            Log::error('Exception in job processing: ' . $e);
        }catch(\Throwable $e){
            Log::error('ExceptionT in job processing: ' . $e);
        }
    }
}
