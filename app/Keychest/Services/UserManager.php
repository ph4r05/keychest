<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;

use App\Keychest\Services\Results\ApiKeySelfRegistrationResult;
use App\Models\ApiKey;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;


class UserManager {

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Create a new manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Returns true if the user new api key self registration is allowed for this request
     * @param $user
     * @param $request
     * @return int
     */
    public function newApiKeySelfRegistrationAllowed($user, $request)
    {
        // Global app switch
        if (!config('keychest.enabled_api_self_register')){
            return ApiKeySelfRegistrationResult::$GLOBAL_DENIAL;
        }

        // TODO: global switch
        // TODO: ip blacklist
        // TODO: rate limiting

        // Number of non-confirmed non-revoked requests - limit.
        $cnt = $this->getNonApprovedApiKeys($user)->count();
        if ($cnt > 100){
            return ApiKeySelfRegistrationResult::$TOO_MANY;
        }

        return ApiKeySelfRegistrationResult::$ALLOWED;
    }

    /**
     * Returns query on the api keys non-revoked, non-approved yet for the user.
     * @param $user
     * @return mixed
     */
    public function getNonApprovedApiKeys($user){
        return $user->apiKeys()->whereNull('revoked_at')->whereNull('verified_at');
    }

    /**
     * Registers a new Api key for the usage.
     * No privilege checking is done. All checks should be done before calling this function.
     *
     * @param $user
     * @param $apiKey
     * @param $request
     * @return ApiKey
     */
    public function registerNewApiKey($user, $apiKey, $request){
        $apiObj = new ApiKey([
            'name' => 'self-registered',
            'api_key' => $apiKey,
            'email_claim' => $user->email,
            'ip_registration' => $request->ip(),
            'user_id' => $user->id,
            'last_seen_active_at' => Carbon::now(),
            'last_seen_ip' => $request->ip()
        ]);

        $user->apiKeys()->save($apiObj);
        return $apiObj;
    }


}
