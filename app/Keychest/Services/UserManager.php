<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;

use App\Keychest\Services\Results\ApiKeySelfRegistrationResult;
use App\Keychest\Services\Results\UserSelfRegistrationResult;
use App\Models\ApiKey;
use App\Models\ApiKeyLog;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


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
     * Checks if a new user can be automatically created via anonymous API call
     * @param $user
     * @param $request
     * @return int
     */
    public function isNewUserRegistrationAllowed($user, $request){
        if (!config('keychest.enabled_user_auto_register')){
            return UserSelfRegistrationResult::$GLOBAL_DENIAL;
        }

        // Allow only certain number of users per the IP address per day.
        $userTbl = User::TABLE;
        $reqs = ApiKeyLog::query()
            ->where('req_ip', '=', $request->ip())
            ->where('action_type', '=', 'new-user')
            ->whereDate('created_at', DB::raw('CURDATE()'))
            ->join($userTbl, function(JoinClause $q){
                $q->on(ApiKeyLog::TABLE.'.req_email', '=', User::TABLE.'.email')
                    ->whereNotNull(User::TABLE.'.auto_created_at');
            })
            ->get();

        if ($reqs->count() > 5){
            return UserSelfRegistrationResult::$TOO_MANY;
        }

        return UserSelfRegistrationResult::$ALLOWED;
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

    /**
     * Function called for user auto-registration - by API request
     * @param $request
     * @param $email
     * @param string|null $challenge reg challenge
     * @return User
     */
    public function registerNewUser($request, $email, $challenge=null){
        $now = Carbon::now();

        // Create log about the creation first
        $log = new ApiKeyLog([
            'created_at' => $now,
            'updated_at' => $now,
            'req_ip' => $request->ip(),
            'req_email' => $email,
            'req_challenge' => $challenge,
            'action_type' => 'new-user'
        ]);
        $log->save();

        // After log is successfully created, create a new user (we have audit record already)
        $u = new User([
            'name' => $email,
            'email' => $email,
            'created_at' => $now,
            'updated_at' => $now,
            'auto_created_at' => $now,
        ]);
        $u->save();

        // Dispatch new user registered events (hooks processors)
        $evt = new Registered($u);
        event($evt);

        return $u;
    }

}
