<?php

namespace App\Http\Middleware;

use App\Http\Request\ParamRequest;
use App\Keychest\Utils\RequestTools;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class JsonResponse
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
        $hdr = $request->header('Accept');

        if (!$request->wantsJson() && (empty($hdr) || Str::startsWith($hdr, '*/'))) {

            Log::debug('JSON response accept changed from hdr: ' . $hdr . ' for ' . $request->url());
            $request->headers->set('Accept', 'application/json', true);

            // Reset cached acceptable content types - derived from Accept header from the request.
            // Accept header can be changed but acceptableContentTypes is constant since filled in (protected var).
            // By setting to null we force it to reload from headers.
            // Ugly reflection hack but creating a new Request does not work here.
            // The controller will get original request object.
            // If the request is ParamRequest we reset it ourselves.
            try {
                RequestTools::tryResetAcceptableContentTypes($request);

            } catch (\Exception $e) {
                Log::error('Exception in setting acceptableContentTypes: ' . $e);
                RequestTools::resetCachedFields($request);
            }
        }

        $response = $next($request);
        return $response;
    }
}
