<?php

namespace App\Http\Controllers;

use App\Http\Requests;

use App\Keychest\Services\EmailManager;
use App\Keychest\Services\LicenseManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;


/**
 * Class LicenseController
 * @package App\Http\Controllers
 */
class LicenseController extends Controller
{
    /**
     * @var LicenseManager
     */
    protected $licenseManager;

    /**
     * @var EmailManager
     */
    protected $emailManager;

    /**
     * Create a new controller instance.
     * @param LicenseManager $manager
     * @param EmailManager $eManager
     */
    public function __construct(LicenseManager $manager, EmailManager $eManager)
    {
        $this->licenseManager = $manager;
        $this->emailManager = $eManager;
    }

    /**
     * Main index view
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(){
        return view('license');
    }

    /**
     * Update current account settings
     */
    public function updateAccount(){
        $allowedInputs = collect(['username', 'notifEmail', 'tz', 'weeklyEnabled']);
        $fieldNames = collect([
            'username' => 'name',
            'notifEmail' => 'notification_email',
            'tz' => 'timezone'
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
}
