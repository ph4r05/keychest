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
 * Class NewApiKeyCreated
 *
 * @package App\Events
 */
class NewApiKeyCreated
{
    use SerializesModels;

    /**
     * The authenticated user.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * Api key created
     *
     * @var \App\Models\ApiKey
     */
    public $apiKey;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ApiKey $apiKey
     */
    public function __construct($user, $apiKey)
    {
        $this->user = $user;
        $this->apiKey = $apiKey;
    }


}
