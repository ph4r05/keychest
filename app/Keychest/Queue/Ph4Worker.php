<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 23.05.17
 * Time: 13:02
 */

namespace App\Keychest\Queue;



use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\QueueManager;
use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;


class Ph4Worker extends Worker
{
    public function __construct(QueueManager $manager, Dispatcher $events, ExceptionHandler $exceptions)
    {
        parent::__construct($manager, $events, $exceptions);
    }

    /**
     * @param WorkerOptions $options
     */
    public function fixSleepOptions(WorkerOptions $options){
        $options->sleep = floatval($options->sleep);
    }

    /**
     * Sleep the script for a given number of seconds.
     *
     * @param  int   $seconds
     * @return void
     */
    public function sleep($seconds)
    {
        if ($seconds < 1) {
            usleep($seconds * 1000000);
        } else {
            sleep($seconds);
        }
    }

    /**
     * Sleep the script for a given number of seconds.
     *
     * @param  int   $microseconds
     * @return void
     */
    public function usleep($microseconds)
    {
        usleep($microseconds);
    }
}

