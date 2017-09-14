<?php

namespace App\Http\Controllers;

use App\Http\Requests;

use App\Keychest\Services\EmailManager;


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
        $res = $this->manager->unsubscribe($token);

        return view('unsubscribe')->with(
            ['token' => $token, 'res' => $res]
        );
    }
}
