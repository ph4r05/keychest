<?php

namespace App\Listeners;

use App\Keychest\Services\UserManager;
use App\Keychest\Utils\UserTools;
use App\Models\UserLoginHistory;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;


use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;

class OnUserLogin
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
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        if (!UserTools::wasUserProperlyRegistered($event->user)){
            event(new Registered($event->user));
        }

        $this->userManager->onUserLogin($event->user, $event);
    }
}
