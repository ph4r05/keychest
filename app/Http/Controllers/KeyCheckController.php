<?php

/*
 * Dashboard controller, loading data.
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Keychest\Services\ScanManager;
use App\Keychest\Utils\DataTools;


use Carbon\Carbon;
use Exception;


use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;


/**
 * Class KeyCheckController
 *
 * @package App\Http\Controllers
 */
class KeyCheckController extends Controller
{
    /**
     * Scan manager
     * @var ScanManager
     */
    protected $scanManager;

    /**
     * Create a new controller instance.
     *
     * @param ScanManager $scanManager
     */
    public function __construct(ScanManager $scanManager)
    {
        $this->middleware('auth');
        $this->scanManager = $scanManager;
    }

    /**
     * Main tester view
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('tester');
    }

    /**
     * Key check request - text
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function keyCheck(Request $request)
    {
        return response()->json(['state' => 'success'], 200);
    }

    /**
     * File upload endpoint
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fileUpload(Request $request)
    {
        return response()->json(['state' => 'success'], 200);
    }

    /**
     * File upload endpoint - API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fileUploadApi(Request $request)
    {
        return response()->json(['state' => 'success'], 200);
    }

    /**
     * PGP key / email check
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pgpCheck(Request $request)
    {
        return response()->json(['state' => 'success'], 200);
    }

    /**
     * Dummy JSON view
     * @return \Illuminate\Http\JsonResponse
     */
    public function dummy()
    {
        return response()->json([], 200);
    }




}