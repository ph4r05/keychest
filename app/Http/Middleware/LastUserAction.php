<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LastUserAction
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
            $oldAction = $user->last_action_at;
            $user->last_action_at = Carbon::now();

            if (empty($oldAction) || $user->last_action_at->diffInSeconds($oldAction->diffIn) > 10) {
                $user->save();
            }
        }

        return $next($request);
    }
}
