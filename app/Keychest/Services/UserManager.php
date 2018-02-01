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
use App\Keychest\Utils\TokenTools;
use App\Keychest\Utils\UserTools;
use App\Models\AccessToken;
use App\Models\ApiKey;
use App\Models\ApiKeyLog;
use App\Models\Owner;
use App\Models\User;
use App\Models\UserLoginHistory;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;


class UserManager {

    const ACTION_BLOCK_ACCOUNT = 'block-account';
    const ACTION_BLOCK_AUTO_API = 'block-autoapi';
    const ACTION_VERIFY_EMAIL = 'verify-email';

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The password token repository.
     *
     * @var \Illuminate\Auth\Passwords\TokenRepositoryInterface
     */
    protected $passwordTokens;

    /**
     * Api Key token manager - verifications
     * @var ApiKeyTokenManager
     */
    protected $apiKeyTokenManager;

    /**
     * User token manager - verifications
     * @var UserTokenManager
     */
    protected $userTokenManager;

    /**
     * Create a new manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->passwordTokens = null; //$this->app->make(TokenRepositoryInterface::class);
    }

    /**
     * Get the default password broker name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['auth.defaults.passwords'];
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
     * Handles user login event
     * @param $user
     * @param null $event
     */
    public function onUserLogin($user, $event=null){
        Log::info('Login event: ' . $user->id);

        $user->last_login_at = $user->cur_login_at;
        $user->cur_login_at = Carbon::now();

        // By the login we verify the user account - has to know the password to login.
        // Designed for auto-registered accounts.
        $user->verified_at = Carbon::now();

        // Also the user block (user blocked keychest from using the account) is reset by the
        // explicit user login to the account.
        $user->blocked_at = null;

        $req = request();
        $user->accredit = $req->session()->get('accredit');

        // Clean some session state
        if ($req){
            $req->session()->forget('verify.email');
        }

        $newLoginRecord = new UserLoginHistory([
            'user_id' => $user->id,
            'login_at' => $user->cur_login_at,
            'login_ip' => $req ? $req->ip() : null,
        ]);

        $newLoginRecord->save();

        $user->last_login_id = $newLoginRecord->id;
        $user->save();
    }

    /**
     * Handles user registration event.
     * @param $user
     * @param null $event
     * @return mixed
     */
    public function onUserRegistered($user, $event=null){
        Log::info('New user registered: ' . $user->id);

        $user->accredit_own = UserTools::accredit($user);
        $user->email_verify_token = UserTools::generateVerifyToken($user);
        $user->weekly_unsubscribe_token = UserTools::generateUnsubscribeToken($user);
        $user->cert_notif_unsubscribe_token = UserTools::generateUnsubscribeToken($user);
        $user->save();

        $owner = new Owner([
            'name' => $user->email,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ]);
        $owner->save();

        $user->primary_owner_id = $owner->id;
        $user->save();
        return $user;
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
        $job = new SendNewUserConfirmation($user, $apiKey, null);
        $job->apiToken = empty($apiKey) ? null : $this->getNewApiKeyToken($apiKey);
        $job->blockAccountToken = $this->getNewUserToken($user, self::ACTION_BLOCK_ACCOUNT);
        $job->emailVerifyToken = $this->getNewUserToken($user, self::ACTION_VERIFY_EMAIL);

        $job->onConnection(config('keychest.wrk_weekly_emails_conn'))
            ->onQueue(config('keychest.wrk_weekly_emails_queue'));

        dispatch($job);
    }

    /**
     * Sends email with API key confirmation / revocation / unsubscribe from these.
     *
     * @param User $user
     * @param ApiKey $apiKey
     */
    public function sendNewApiKeyConfirmation(User $user, ApiKey $apiKey, $request){
        $job = new SendNewApiKeyConfirmation($user, $apiKey, null);
        $job->apiToken = $this->getNewApiKeyToken($apiKey);
        $job->blockApiToken = $this->getNewUserToken($user, [
            'action' => self::ACTION_BLOCK_AUTO_API,
            'multiple' => true
        ]);

        $job->onConnection(config('keychest.wrk_weekly_emails_conn'))
            ->onQueue(config('keychest.wrk_weekly_emails_queue'));

        dispatch($job);
    }

    /**
     * Performs the token verification.
     * If token is valid, user is returned.
     * @param $token
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function checkVerifyToken($token){
        return empty($token) ? null :
            User::query()->where('email_verify_token', '=', $token)->first();
    }

    /**
     * Performs the User token verification.
     * Does not invalidate the token.
     *
     * @param $token
     * @param $action
     * @return AccessToken|null
     */
    public function checkUserToken($token, $action){
        $mgr = $this->getUserTokenManager();
        $token = $mgr->get($token, [
            'action' => $action
        ]);

        return $token;
    }

    /**
     * Performs the API token verification.
     * Does not invalidate the token.
     *
     * @param $apiKeyToken
     * @return AccessToken|null
     */
    public function checkApiToken($apiKeyToken){
        $mgr = $this->getApiKeyTokenManager();
        $token = $mgr->get($apiKeyToken);
        return $token;
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
        $token = $this->checkUserToken($token, self::ACTION_BLOCK_ACCOUNT);
        if (!$token){
            return null;
        }

        $u = $token->user;
        TokenTools::tokenUsed($token);

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
        $token = $this->checkUserToken($token, self::ACTION_VERIFY_EMAIL);
        if (!$token){
            return null;
        }

        $u = $token->user;
        TokenTools::tokenUsed($token);

        $u->blocked_at = null;
        $u->email_verified_at = Carbon::now();
        $u->verified_at = Carbon::now();
        $u->email_verify_token = UserTools::generateVerifyToken($u);
        $u->save();

        return $u;
    }

    /**
     * Blocks API key auto-registration via API.
     *
     * @param $token
     * @param null $request
     * @return User|null
     */
    public function blockAutoApiKeys($token, $request=null)
    {
        $token = $this->checkUserToken($token, self::ACTION_BLOCK_AUTO_API);
        if (!$token){
            return null;
        }

        $u = $token->user;
        TokenTools::tokenUsed($token);

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
        $token = $this->checkApiToken($apiKeyToken);
        if (!$token){
            return null;
        }

        $apiKey = $token->apiKey;

        // Create log about the creation first
        ApiKeyLogger::create()
            ->request($request)
            ->apiKey($apiKey)
            ->action('confirm-apikey')
            ->save();

        TokenTools::tokenUsed($token);

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
        $token = $this->checkApiToken($apiKeyToken);
        if (!$token){
            return null;
        }

        $apiKey = $token->apiKey;

        TokenTools::tokenUsed($token);

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

    /**
     * Creates own token repository.
     * A bit hacky way to get to the token repository.
     *
     * @return DatabaseTokenRepository|TokenRepositoryInterface|null
     */
    protected function getPasswordTokens(){
        if ($this->passwordTokens){
            return $this->passwordTokens;
        }

        $name = $this->getDefaultDriver();
        $config = $this->app['config']["auth.passwords.{$name}"];
        if (is_null($config)) {
            throw new InvalidArgumentException("Password resetter [{$name}] is not defined.");
        }

        $key = $this->app['config']['app.key'];
        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        $connection = isset($config['connection']) ? $config['connection'] : null;
        $this->passwordTokens = new DatabaseTokenRepository(
            $this->app['db']->connection($connection),
            $this->app['hash'],
            $config['table'],
            $key,
            $config['expire']
        );

        return $this->passwordTokens;
    }

    /**
     * Returns local API key token manager.
     * @return ApiKeyTokenManager
     */
    public function getApiKeyTokenManager(){
        if (!$this->apiKeyTokenManager){
            $this->apiKeyTokenManager = $this->app->make(ApiKeyTokenManager::class);
        }

        return $this->apiKeyTokenManager;
    }

    /**
     * Returns local API key token manager.
     * @return UserTokenManager
     */
    public function getUserTokenManager(){
        if (!$this->userTokenManager){
            $this->userTokenManager = $this->app->make(UserTokenManager::class);
        }

        return $this->userTokenManager;
    }

    /**
     * Creates a new reset token for the user.
     * @param User $user
     * @return string
     */
    public function newPasswordToken(User $user){
        return $this->getPasswordTokens()->create($user);
    }

    /**
     * Creates new token for the User
     * @param User $user
     * @param null $options
     * @return string
     */
    protected function getNewUserToken(User $user, $options=null){
        if (is_string($options)){
            $options = ['action' => $options];
        }
        return $this->getUserTokenManager()->create($user, $options)->getTokenToSend();
    }

    /**
     * Creates new confirmation token for the API key
     * @param ApiKey $apiKey
     * @param null $options
     * @return string
     */
    protected function getNewApiKeyToken(ApiKey $apiKey, $options=null){
        if (is_string($options)){
            $options = ['action' => $options];
        }
        return $this->getApiKeyTokenManager()->create($apiKey, $options)->getTokenToSend();
    }
}
