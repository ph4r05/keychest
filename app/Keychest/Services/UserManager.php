<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;

use App\Events\NewApiKeyCreated;
use App\Jobs\SendNewApiKeyConfirmation;
use App\Jobs\SendNewUserConfirmation;
use App\Keychest\Services\Results\ApiKeySelfRegistrationResult;
use App\Keychest\Services\Results\UserSelfRegistrationResult;
use App\Keychest\Utils\ApiKeyLogger;
use App\Keychest\Utils\UserTools;
use App\Models\ApiKey;
use App\Models\ApiKeyLog;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


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

        // TODO: ip blacklist
        // TODO: rate limiting

        // Check if user has blocked the API key creation
        if (!empty($user->deleted_at)
            || !empty($user->closed_at)
            || !empty($user->blocked_at)
            || $user->new_api_keys_state)
        {
            return ApiKeySelfRegistrationResult::$USER_BLOCK;
        }

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
        $q = ApiKeyLog::query()
            ->where(ApiKeyLog::TABLE.'.req_ip', '=', $request->ip())
            ->where(ApiKeyLog::TABLE.'.action_type', '=', 'new-user')
            ->whereDate(ApiKeyLog::TABLE.'.created_at', DB::raw('CURDATE()'))
            ->join($userTbl, function(JoinClause $q){
                $q->on(ApiKeyLog::TABLE.'.req_email', '=', User::TABLE.'.email')
                    ->whereNotNull(User::TABLE.'.auto_created_at');
            });

        $reqs = $q->get();
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
        $now = Carbon::now();

        // Create log about the creation first
        ApiKeyLogger::create()
            ->now($now)
            ->request($request)
            ->user($user)
            ->challenge($apiKey)
            ->action('new-apikey')
            ->save();

        // After log is successfully created, create a api key (we have audit record already)
        $apiObj = new ApiKey([
            'name' => 'self-registered',
            'api_key' => $apiKey,
            'api_verify_token' => UserTools::generateVerifyToken(),
            'email_claim' => $user->email,
            'ip_registration' => $request->ip(),
            'user_id' => $user->id,
            'last_seen_active_at' => $now,
            'last_seen_ip' => $request->ip()
        ]);

        $user->apiKeys()->save($apiObj);

        // New event dispatch
        $evt = new NewApiKeyCreated($user, $apiObj);
        event($evt);

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
        ApiKeyLogger::create()
            ->now($now)
            ->request($request)
            ->email($email)
            ->challenge($challenge)
            ->action('new-user')
            ->save();

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

    /**
     * Sends new user confirmation email.
     * User should confirm the account being created for him is a legit request.
     * User should be able to confirm a) account & change password b) confirm API request c) unsubscribe
     *
     * In the initial version a) is merged with b)
     *
     * @param User $user
     * @param ApiKey $apiKey
     */
    public function sendNewUserConfirmation(User $user, ApiKey $apiKey, $request){
        $job = new SendNewUserConfirmation($user, $apiKey, $request);
        $job->onConnection(config('keychest.wrk_weekly_emails_conn'))
            ->onQueue(config('keychest.wrk_weekly_emails_queue'));
    }

    /**
     * Sends email with API key confirmation / revocation / unsubscribe from these.
     *
     * @param User $user
     * @param ApiKey $apiKey
     */
    public function sendNewApiKeyConfirmation(User $user, ApiKey $apiKey, $request){
        $job = new SendNewApiKeyConfirmation($user, $apiKey, $request);
        $job->onConnection(config('keychest.wrk_weekly_emails_conn'))
            ->onQueue(config('keychest.wrk_weekly_emails_queue'));
    }

    /**
     * Blocking Keychest from using this account for any automated purpose.
     * User can decide to let block the account if someone registers his email by mistake / on his behalf
     * without his consent.
     * Verification token is re-generated.
     *
     * @param $token
     * @param null $request
     * @return User
     */
    public function block($token, $request=null)
    {
        $u = User::query()->where('email_verify_token', '=', $token)->first();
        if (!$u){
            return null;
        }

        $u->blocked_at = Carbon::now();
        $u->email_verify_token = UserTools::generateVerifyToken($u);
        $u->save();

        return $u;
    }

    /**
     * Verify user account by the email verification.
     * Verification token is re-generated.
     *
     * @param $token
     * @param null $request
     * @return User|null
     */
    public function verifyEmail($token, $request=null)
    {
        $u = User::query()->where('email_verify_token', '=', $token)->first();
        if (!$u){
            return null;
        }

        $u->blocked_at = null;
        $u->email_verified_at = Carbon::now();
        $u->verified_at = Carbon::now();
        $u->email_verify_token = UserTools::generateVerifyToken($u);
        $u->save();

        return $u;
    }

    /**
     * Blocks API key auto-registration via API.
     * TODO: separate verification token?
     *
     * @param $token
     * @param null $request
     * @return User|null
     */
    public function blockAutoApiKeys($token, $request=null)
    {
        $u = User::query()->where('email_verify_token', '=', $token)->first();
        if (!$u){
            return null;
        }

        $u->new_api_keys_state = 1;
        $u->save();

        return $u;
    }

    /**
     * Confirms API key
     * @param $apiKeyToken
     * @param null $request
     * @return ApiKey|null
     */
    public function confirmApiKey($apiKeyToken, $request=null)
    {
        $apiKey = ApiKey::query()->where('api_verify_token', '=', $apiKeyToken)->first();
        if (!$apiKey){
            return null;
        }

        // Create log about the creation first
        ApiKeyLogger::create()
            ->request($request)
            ->apiKey($apiKey)
            ->action('confirm-apikey')
            ->save();

        // Modify the api key
        $apiKey->verified_at = Carbon::now();
        $apiKey->revoked_at = null;
        $apiKey->save();

        return $apiKey;
    }

    /**
     * Revokes the API key token
     * @param $apiKeyToken
     * @param null $request
     * @return ApiKey|null
     */
    public function revokeApiKey($apiKeyToken, $request=null)
    {
        $apiKey = ApiKey::query()->where('api_verify_token', '=', $apiKeyToken)->first();
        if (!$apiKey){
            return null;
        }

        // Create log about the creation first
        ApiKeyLogger::create()
            ->request($request)
            ->apiKey($apiKey)
            ->action('revoke-apikey')
            ->save();

        // Modify the api key
        $apiKey->revoked_at = Carbon::now();
        $apiKey->save();

        return $apiKey;
    }

}
