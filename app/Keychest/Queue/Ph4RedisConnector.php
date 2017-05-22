<?php

namespace App\Keychest\Queue;

use App\Keychest\Queue\Ph4RedisQueue;
use Illuminate\Support\Arr;
use Illuminate\Queue\Connectors\RedisConnector;
use Illuminate\Contracts\Redis\Factory as Redis;

class Ph4RedisConnector extends RedisConnector
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new Ph4RedisQueue(
            $this->redis, $config['queue'],
            Arr::get($config, 'connection', $this->connection),
            Arr::get($config, 'retry_after', 60)
        );
    }
}
