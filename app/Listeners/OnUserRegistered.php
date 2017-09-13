<?php

namespace App\Listeners;

use App\Keychest\Utils\UserTools;
use Illuminate\Auth\Events\Registered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class OnUserRegistered
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        $event->user->accredit_own = UserTools::accredit($event->user);
        $event->user->email_verify_token = UserTools::generateVerifyToken($event->user);
        $event->user->weekly_unsubscribe_token = UserTools::generateUnsubscribeToken($event->user);
        $event->user->save();
    }
}
