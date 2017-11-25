<?php

namespace App\Listeners;

use App\Keychest\Utils\UserTools;
use App\Models\Owner;
use Illuminate\Auth\Events\Registered;


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
        $event->user->cert_notif_unsubscribe_token = UserTools::generateUnsubscribeToken($event->user);
        $event->user->save();

        $owner = new Owner([
            'name' => $event->user->email,
            'created_at' => $event->user->created_at,
            'updated_at' => $event->user->updated_at
        ]);
        $owner->save();

        $event->user->primary_owner_id = $owner->id;
        $event->user->save();
    }
}
