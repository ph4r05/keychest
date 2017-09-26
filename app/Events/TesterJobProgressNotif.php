<?php

namespace App\Events;

use App\Keychest\Queue\JsonJob;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

use Illuminate\Support\Facades\Log;

/**
 * Class TesterJobProgressNotif
 * Broadcast event used for rebroadcasting events from the Keychest Scanner to the Websocket server.
 * ShouldBroadcastNow - the listener is picked in the background worker already, we don't want
 * another Redis round trip.
 *
 * @package App\Events
 */
class TesterJobProgressNotif implements JsonJob, ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * json encoded data passed by python scanner
     * @var string
     */
    protected $json_data;

    /**
     * Mixed decoded data
     * Public so event has this field in the payload
     * @var mixed|null
     */
    public $data;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Initializes notification event with the json data.
     * @param TesterJobProgress $evt
     * @return TesterJobProgressNotif
     */
    public static function fromEvent(TesterJobProgress $evt){
        $e = new TesterJobProgressNotif();
        $e->json_data = $evt->getJsonData();
        return $e;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        $data = $this->getData();
        if (empty($data) || !isset($data->job)){
            return new PrivateChannel('keytest');
        }

        return new Channel('keytest.' . $data->job);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'keytest.event';
    }

    /**
     * Returns json interpretation
     * @return array
     */
    public function toJson()
    {
        return $this->getData();
    }

    /**
     * @return string
     */
    public function getJsonData()
    {
        return $this->json_data;
    }

    /**
     * Attempts to decode json data
     * @return mixed|null
     */
    public function getData()
    {
        if ($this->data !== null){
            return $this->data;
        }

        try{
            $this->data = json_decode($this->getJsonData());
        } catch(\Exception $e){
            Log::error('Exception in event data decode: ' . $e);
        }

        return $this->data;
    }
}
