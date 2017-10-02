<?php

/*
 * Dashboard controller, loading data.
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Keychest\Services\AnalysisManager;
use App\Keychest\Services\ApiManager;
use App\Keychest\Services\Exceptions\CertificateAlreadyInsertedException;
use App\Keychest\Services\LicenseManager;
use App\Keychest\Services\ScanManager;
use App\Keychest\Services\ServerManager;
use App\Keychest\Services\UserManager;
use App\Keychest\Utils\CertificateTools;
use App\Keychest\Utils\DomainTools;
use App\Keychest\Utils\Exceptions\MultipleCertificatesException;
use Exception;
use Illuminate\Http\Request;
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
     * @var ApiManager
     */
    protected $apiManager;

    /**
     * Create a new controller instance.
     *
     * @param ScanManager $scanManager
     * @param AnalysisManager $analysisManager
     * @param UserManager $userManager
     * @param LicenseManager $licenseManager
     * @param ServerManager $serverManager
     * @param ApiManager $apiManager
     */
    public function __construct(ScanManager $scanManager, AnalysisManager $analysisManager,
                                UserManager $userManager, LicenseManager $licenseManager,
                                ServerManager $serverManager, ApiManager $apiManager)
    {
        $this->scanManager = $scanManager;
        $this->analysisManager = $analysisManager;
        $this->userManager = $userManager;
        $this->apiManager = $apiManager;
    }

    /**
     * Confirms the API key
     * Returns view.
     *
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
     * Returns view.
     *
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

    // ------------------------------------------------------------------------------------------------------------
    // API related part

    /**
     * Basic API auth test
     * @param Request $request
     * @return null
     */
    public function user(Request $request){
        $u = $request->user();
        return response()->json(['user' => empty($u) ? null : $u->email], 200);
    }

    /**
     * Adds certificate to the monitoring, via waiting object.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCertificate(Request $request){
        $this->validate($request, [
            'certificate' => 'bail|required|min:24'
        ]);

        $user = $request->user();
        $certificate = Input::get('certificate');

        // Add request for waiting object.
        try {
            // Basic certificate processing
            $certificate = CertificateTools::sanitizeCertificate($certificate);

            // Insert waiting object
            $apiObj = $this->apiManager->addCertificateToWatch($user, $user->apiKey, $certificate, $request);

            return response()->json([
                'status' => 'success',
                'id' => $apiObj->waiting_id,
                'key' => $apiObj->object_key,
            ], 200);

        } catch (CertificateAlreadyInsertedException $e){
            return response()->json(['status' => 'already-inserted'], 409);

        } catch (MultipleCertificatesException $e){
            return response()->json(['status' => 'single-cert-expected'], 406);

        } catch (Exception $e){
            Log::error($e);
            return response()->json(['status' => 'error'], 500);

        }
    }

    /**
     * Adds domain to the monitoring, via waiting object.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addDomain(Request $request){
        $this->validate($request, [
            'domain' => 'bail|required|min:3'
        ]);

        $domain = strtolower(trim(Input::get('domain')));
        $server = DomainTools::normalizeUserDomainInput($domain);
        $parsed = parse_url($server);

        if (empty($id) || empty($parsed) || !DomainTools::isValidParsedUrlHostname($parsed)){
            return response()->json(['status' => 'invalid-domain'], 406);
        }

        // Add request for waiting object.
        try {
            $user = $request->user();

            $apiObj = $this->apiManager->addDomainToWatch($user, $user->apiKey, $domain, $request);

            return response()->json([
                'status' => 'success',
                'id' => $apiObj->waiting_id,
                'key' => $apiObj->object_key,
                'domain' => $domain
            ], 200);

        } catch (Exception $e){
            Log::error($e);
            return response()->json(['status' => 'error'], 500);

        }
    }

    /**
     * Checks the domain
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function domainCertExpiration(Request $request){
        return response()->json(['status' => 'not-implemented'], 501);
    }
}
