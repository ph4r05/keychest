<?php

namespace App\Keychest\Queue;




use Illuminate\Queue\RedisQueue;

use Illuminate\Contracts\Queue\Queue as QueueContract;

class Ph4RedisQueue extends RedisQueue implements QueueContract
{
    /**
     * Create a payload string from the given job and data.
     *
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @return array
     */
    protected function createPayloadArray($job, $data = '', $queue = null)
    {
        $p = parent::createPayloadArray($job, $data, $queue);
        if (is_object($job) && $job instanceof JsonJob){
            $p['data']['json'] = $job->toJson();
        }

        return $p;
    }



}
