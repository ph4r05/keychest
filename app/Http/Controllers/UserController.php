<?php

namespace App\Http\Controllers;

use App\Http\Requests;

use App\Keychest\Services\EmailManager;
use App\Keychest\Services\LicenseManager;
use App\Keychest\Services\UserManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;


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
}
