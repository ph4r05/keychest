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
     * Dummy JSON view
     * @return \Illuminate\Http\JsonResponse
     */
    public function dummy()
    {
        return response()->json([], 200);
    }




}