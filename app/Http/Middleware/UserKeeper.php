<?php

namespace App\Http\Middleware;

use App\Keychest\Utils\UserTools;
use Carbon\Carbon;
use Closure;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class UserKeeper
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            $user = Auth::getUser();

            if (!UserTools::wasUserProperlyRegistered($user)){
                event(new Registered($user));
            }
        }

        return $next($request);
    }
}
