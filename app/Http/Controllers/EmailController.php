<?php

namespace App\Http\Controllers;

use App\Http\Requests;

use App\Keychest\Services\EmailManager;
use Illuminate\Support\Facades\Input;


/**
 * Class EmailController
 * @package App\Http\Controllers
 */
class EmailController extends Controller
{
    /**
     * @var EmailManager
     */
    protected $manager;

    /**
     * Create a new controller instance.
     * @param EmailManager $manager
     */
    public function __construct(EmailManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Unsubscribe from email report
     * @param $token
     * @return $this
     */
    public function unsubscribe($token){
        $user = $this->manager->checkUnsunbscribeToken($token);
        $confirm = boolval(Input::get('confirm'));
        $res = null;

        if ($confirm) {
            $res = $this->manager->unsubscribe($token);
        }

        return view('unsubscribe')->with(
            [
                'token' => $token,
                'confirm' => $confirm,
                'user' => $user,
                'alreadyDisabled' => $user->weekly_emails_disabled,
                'res' => $res
            ]
        );
    }
}
