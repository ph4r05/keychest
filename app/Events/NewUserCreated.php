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
 * Class NewUserCreated
 *
 * @package App\Events
 */
class NewUserCreated
{
    use SerializesModels;

    /**
     * The authenticated user.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\User  $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }


}
