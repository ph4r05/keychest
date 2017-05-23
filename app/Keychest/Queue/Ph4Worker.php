<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 23.05.17
 * Time: 13:02
 */

namespace App\Keychest\Queue;

use App\Keychest\Queue\JsonJob;
use App\Keychest\Queue\Ph4RedisJob;
use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Queue\RedisQueue;
use Illuminate\Contracts\Redis\Factory as Redis;
use Illuminate\Contracts\Queue\Queue as QueueContract;

class Ph4Worker extends Worker
{
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
        usleep(floatval($seconds) * 1e6);
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

