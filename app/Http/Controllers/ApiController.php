<?php

/*
 * Dashboard controller, loading data.
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Keychest\DataClasses\ValidityDataModel;
use App\Keychest\Services\AnalysisManager;
use App\Keychest\Services\LicenseManager;
use App\Keychest\Services\ScanManager;
use App\Keychest\Services\ServerManager;
use App\Keychest\Services\UserManager;
use App\Keychest\Utils\DataTools;


use App\Models\User;
use Barryvdh\Debugbar\LaravelDebugbar;
use Barryvdh\Debugbar\Middleware\Debugbar;
use Carbon\Carbon;
use Exception;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;


/**
 * Class ApiController
 * Common API controller for smaller tasks
 *
 * @package App\Http\Controllers
 */
class ApiController extends Controller
{
    /**
     * Scan manager
     * @var ScanManager
     */
    protected $scanManager;

    /**
     * @var AnalysisManager
     */
    protected $analysisManager;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * Create a new controller instance.
     *
     * @param ScanManager $scanManager
     */
    public function __construct(ScanManager $scanManager, AnalysisManager $analysisManager,
                                UserManager $userManager, LicenseManager $licenseManager,
                                ServerManager $serverManager)
    {
        $this->scanManager = $scanManager;
        $this->analysisManager = $analysisManager;
        $this->userManager = $userManager;
    }

    /**
     * Confirms the API key
     * @param $apiKeyToken
     * @return $this
     */
    public function confirmApiKey($apiKeyToken){
        $token = $this->userManager->checkApiToken($apiKeyToken);
        $confirm = boolval(Input::get('confirm'));
        $res = $token ? $token->apiKey : null;

        if ($confirm) {
            $res = $this->userManager->confirmApiKey($apiKeyToken);
        }

        return view('account.confirm_api_key_main')->with(
            [
                'apiKeyToken' => $apiKeyToken,
                'apiKey' => $res,
                'confirm' => $confirm,
                'res' => $res
            ]
        );
    }

    /**
     * Revokes the API key
     * @param $apiKeyToken
     * @return $this
     */
    public function revokeApiKey($apiKeyToken){
        $token = $this->userManager->checkApiToken($apiKeyToken);
        $confirm = boolval(Input::get('confirm'));
        $res = $token ? $token->apiKey : null;

        if ($confirm) {
            $res = $this->userManager->revokeApiKey($apiKeyToken);
        }

        return view('account.revoke_api_key_main')->with(
            [
                'apiKeyToken' => $apiKeyToken,
                'apiKey' => $res,
                'confirm' => $confirm,
                'res' => $res
            ]
        );
    }


}