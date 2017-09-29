<?php

namespace App\Http\Controllers;

use App\Keychest\Services\EmailManager;
use App\Keychest\Services\LicenseManager;
use App\Keychest\Services\Results\UserSelfRegistrationResult;
use App\Keychest\Services\UserManager;
use App\Models\ApiKey;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use App\Keychest\Services\Results\ApiKeySelfRegistrationResult;
use App\Keychest\Services\Exceptions\CouldNotRegisterNewUserException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;


/**
 * Class LicenseController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    /**
     * @var LicenseManager
     */
    protected $licenseManager;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var EmailManager
     */
    protected $emailManager;

    /**
     * Create a new controller instance.
     * @param UserManager $userManager
     * @param LicenseManager $licenseManager
     * @param EmailManager $eManager
     */
    public function __construct(UserManager $userManager, LicenseManager $licenseManager, EmailManager $eManager)
    {
        $this->userManager = $userManager;
        $this->licenseManager = $licenseManager;
        $this->emailManager = $eManager;
    }

    /**
     * Update current account settings
     */
    public function updateAccount(){
        $allowedInputs = collect(['username', 'notifEmail', 'tz', 'weeklyEnabled', 'notifType']);
        $fieldNames = collect([
            'username' => 'name',
            'notifEmail' => 'notification_email',
            'tz' => 'timezone',
            'notifType' => 'cert_notif_state',
        ]);

        $data = $allowedInputs->mapWithKeys(function($item){
            $val = trim(Input::get($item));
            if (empty($val)){
                return [];
            }

            return [$item => $val];
        });

        $data = $data->mapWithKeys(function($item, $key) use ($fieldNames) {
            if ($key == 'weeklyEnabled'){
                return ['weekly_emails_disabled' => !intval($item)];
            }

            return [$fieldNames->get($key, $key) => $item];
        });

        if (empty($data)){
            return response()->json(['status' => 'success'], 200);
        }

        $curUser = Auth::user();
        foreach ($data as $key => $value){
            $curUser->$key = $value;
        }
        $curUser->save();

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Close account request
     * JSON API request, called by authenticated JS client
     */
    public function closeAccount(){
        $curUser = Auth::user();
        $curUser->closed_at = Carbon::now();
        $curUser->save();

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Verify user account with email verification.
     *
     * @param Request $request
     * @param null|string $token
     * @param null|string $apiKeyToken
     * @return \Illuminate\Contracts\View\View|\Illuminate\View\View
     */
    public function verifyEmail(Request $request, $token=null, $apiKeyToken=null){
        $tokenObj = $this->userManager->checkUserToken($token, UserManager::ACTION_VERIFY_EMAIL);
        $confirm = boolval(Input::get('confirm', '0'));
        $res = $tokenObj ? $tokenObj->user : null;
        $apiKeyObj = null;

        // Clear the state
        $request->session()->forget('verify.email.verified');

        // Confirmation from the user to perform the change
        if ($confirm){
            $res = $this->userManager->verifyEmail($token);

            // Verify API call?
            if ($res && !empty($apiKeyToken)){
                $apiKeyObj = $this->userManager->confirmApiKey($apiKeyToken);
            }

            // Success change -> redirect to success page + password set page.
            if ($res) {
                $request->session()->put('verify.email.verified', $res->id);
                $request->session()->put('verify.email.verified_at', time());
                return redirect()->route('email.verified');
            }
        }

        return view('account.verify_email_main')->with(
            [
                'token' => $token,
                'apiKey' => $apiKeyObj,
                'apiKeyToken' => $apiKeyToken,
                'confirm' => $confirm,
                'res' => $res]
        );
    }

    /**
     * Email verified page, success info + password change step.
     * @param Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\View\View
     */
    public function emailVerified(Request $request){
        $sess = $request->session();

        $verifiedId = $sess->get('verify.email.verified');
        $res = empty($verifiedId) ? null : User::find($verifiedId);

        $data = [
            'token' => null,
            'apiKey' => null,
            'apiKeyToken' => null,
            'confirm' => 1,
            'res' => $res
        ];

        if ($res){
            $newToken = $sess->get('verify.email.new_token');
            if (empty($newToken)) {
                $newToken = $this->userManager->newPasswordToken($res);
            }

            $data['token'] = $newToken;
            $sess->flash('verify.email.new_token', $newToken);
        }

        return view('account.verify_email_main')->with($data);
    }

    /**
     * Blocking Keychest from using this account for any automated purpose.
     * User can decide to let block the account if someone registers his email by mistake / on his behalf
     * without his consent.
     *
     * @param $token
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\View
     */
    public function blockAccount($token){
        $tokenObj = $this->userManager->checkUserToken($token, UserManager::ACTION_BLOCK_ACCOUNT);
        $confirm = boolval(Input::get('confirm'));
        $res = $tokenObj ? $tokenObj->user : null;

        if ($confirm) {
            $res = $this->userManager->block($token);
        }

        return view('account.block_account_main')->with(
            [
                'token' => $token,
                'confirm' => $confirm,
                'res' => $res
            ]
        );
    }

    /**
     * Blocks unsolicited creation of another API keys
     * @param $token
     * @return $this
     */
    public function blockAutoApiKeys($token){
        $tokenObj = $this->userManager->checkUserToken($token, UserManager::ACTION_BLOCK_AUTO_API);
        $confirm = boolval(Input::get('confirm'));
        $res = $tokenObj ? $tokenObj->user : null;

        if ($confirm) {
            $res = $this->userManager->blockAutoApiKeys($token);
        }

        return view('account.block_auto_api_key_main')->with(
            [
                'token' => $token,
                'confirm' => $confirm,
                'res' => $res
            ]
        );
    }

    /**
     * Claim new API key access - limited functionality until verified / confirmed.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function claimAccess(Request $request){
        $this->validate($request, [
            'email' => 'bail|required|email|max:255',
            'api_key' => 'bail|required|max:64|min:16'
        ]);

        $bailResponse = response()->json(['status' => 'not-allowed'], 405);
        $errorResponse = response()->json(['status' => 'error'], 503);
        $successResponse = response()->json(['status' => 'success'], 200);

        $email = Input::get('email');
        $apiKey = Input::get('api_key');

        // Load user by the email, user has to exist before this call.
        // If user does not exist and system policy allows it a new user is created.
        $userExisted = true;
        $user = User::query()->where('email', '=', $email)->first();
        if (empty($user)){
            Log::debug('User with email not found: ' . $email);

            try {
                $user = $this->claimAccessNewUser($request, $email, $apiKey);
                $userExisted = false;

            } catch (CouldNotRegisterNewUserException $e){
                Log::warning('Could not auto-register create a new user: ' . $email . ' ex: ' . $e->getMessage());
                return $bailResponse;

            } catch (\Exception $e){
                Log::warning('Could not auto-register create a new user: ' . $email . ' ex: ' . $e);
                return $errorResponse;
            }
        }

        // Check if there is such api code already. If exists, return success.
        // If the code is revoked, return not-allowed.
        $apiKeyObj = $user->apiKeys()->where('api_key', '=', $apiKey)->first();
        if ($apiKeyObj){
            Log::debug("User ${email} has existing api key ${apiKey}");
            return empty($apiKeyObj->revoked_at) ? $successResponse : $bailResponse;
        }

        // Check if the api key self registration is allowed (block, blacklist, denial, too many)
        $newApiAllowedStatus = $this->userManager->newApiKeySelfRegistrationAllowed($user, $request);
        if ($newApiAllowedStatus == ApiKeySelfRegistrationResult::$TOO_MANY){
            return response()->json(['status' => 'too-many'], 405);
        } elseif ($newApiAllowedStatus != ApiKeySelfRegistrationResult::$ALLOWED){
            return $bailResponse;
        }

        // Create a new API key if the user has allowed it.
        try {
            $apiKeyObj = $this->userManager->registerNewApiKey($user, $apiKey, $request);

        } catch (\Exception $e){
            Log::warning('Could not auto-register API key, email: ' . $email . ' api: ' . $apiKey . ' ex: ' . $e);
            return $errorResponse;
        }

        // New user & api key ? send registration email with confirmation & password set / unsubscribe.
        if (!$userExisted){
            $this->userManager->sendNewUserConfirmation($user, $apiKeyObj, $request);

        } else {
            // existing user & api key ? send confirm email / unsubscribe link / block.
            $this->userManager->sendNewApiKeyConfirmation($user, $apiKeyObj, $request);
        }

        return response()->json([
            'status' => 'created',
            'user' => $user->email,
            'apiKey' => $apiKeyObj->api_key
        ], 200);
    }

    /**
     * Auto-registers a new user for the given email.
     * @param Request $request
     * @param $email
     * @param $apiKey
     * @return User
     * @throws CouldNotRegisterNewUserException
     */
    protected function claimAccessNewUser(Request $request, $email, $apiKey){
        $allowNewUserResult = $this->userManager->isNewUserRegistrationAllowed($email, $request);
        if ($allowNewUserResult !== UserSelfRegistrationResult::$ALLOWED){
            throw new CouldNotRegisterNewUserException(
                'New user registration is not allowed by the system policy, code: ' . $allowNewUserResult);
        }

        return $this->userManager->registerNewUser($request, $email, $apiKey);
    }

}
