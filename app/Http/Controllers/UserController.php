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
     */
    public function closeAccount(){
        $curUser = Auth::user();
        $curUser->closed_at = Carbon::now();
        $curUser->save();

        return response()->json(['status' => 'success'], 200);
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
            'api_key' => 'bail|required|max:64'
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
                $userExisted = true;

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

        // TODO: new user & api key ? send registration email with confirmation & password set / unsubscribe
        // TODO: existing user & api key ? send confirm email / unsubscribe link / block.
        return response()->json(['status' => 'created'], 200);
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
