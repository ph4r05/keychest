<?php

namespace App\Events;

use App\Keychest\Queue\JsonJob;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ScanJobProgress implements JsonJob
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * json encoded data passed by python scanner
     * @var string
     */
    protected $json_data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    /**
     * Returns json interpretation
     * @return array
     */
    public function toJson()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getJsonData()
    {
        return $this->json_data;
    }


}
