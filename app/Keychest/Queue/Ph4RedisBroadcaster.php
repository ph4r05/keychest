<?php

namespace App\Keychest\Queue;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Broadcasting\Broadcasters\RedisBroadcaster;
use Illuminate\Contracts\Redis\Factory as Redis;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Ph4RedisBroadcaster extends RedisBroadcaster
{
    /**
     * Broadcast the given event.
     *
     * @param  array  $channels
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $connection = $this->redis->connection($this->connection);

        $payload = json_encode([
            'event' => $event,
            'data' => $payload,
            'socket' => Arr::pull($payload, 'socket'),
        ]);

        foreach ($this->formatChannels($channels) as $channel) {
            $connection->publish($channel, $payload);
        }
    }
}
