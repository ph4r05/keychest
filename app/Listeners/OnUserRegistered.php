<?php

namespace App\Listeners;

use App\Keychest\Services\UserManager;
use App\Keychest\Utils\UserTools;
use App\Models\Owner;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;


class OnUserRegistered
{
    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * Create the event listener.
     * @param UserManager $userManager
     */
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Handle the event.
     *
     * @param  Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        $this->userManager->onUserRegistered($event->user, $event);
    }
}
