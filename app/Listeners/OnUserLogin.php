<?php

namespace App\Listeners;

use App\Models\UserLoginHistory;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;


use Illuminate\Support\Facades\Log;

class OnUserLogin
{
    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        Log::info('Login event!');
        $event->user->last_login_at = $event->user->cur_login_at;
        $event->user->cur_login_at = Carbon::now();

        // By the login we verify the user account - has to know the password to login.
        // Designed for auto-registered accounts.
        $event->user->verified_at = Carbon::now();

        // Also the user block (user blocked keychest from using the account) is reset by the
        // explicit user login to the account.
        $event->user->blocked_at = null;

        $req = request();
        $event->user->accredit = $req->session()->get('accredit');

        // Clean some session state
        if ($req){
            $req->session()->forget('verify.email');
        }

        $newLoginRecord = new UserLoginHistory([
            'user_id' => $event->user->id,
            'login_at' => $event->user->cur_login_at,
            'login_ip' => $req ? $req->ip() : null,
        ]);

        $newLoginRecord->save();
        
        $event->user->last_login_id = $newLoginRecord->id;
        $event->user->save();
    }
}
