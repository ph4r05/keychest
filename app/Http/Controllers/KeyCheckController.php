<?php

/*
 * Dashboard controller, loading data.
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Jobs\TesterJob;
use App\Keychest\DataClasses\KeyToTest;
use App\Keychest\Services\ScanManager;
use App\Keychest\Utils\DataTools;


use Carbon\Carbon;
use Exception;


use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Webpatser\Uuid\Uuid;


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
        // $this->middleware('auth');
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
        $this->validate($request, [
            'key' => 'max:1000000',
            'keyType' => 'max:1000',
            'uuid'  => 'min:24|max:128'
        ]);

        $key = Input::get('key');
        $keys = Input::get('keys');
        $keyType = Input::get('keyType');

        $job = $this->newTest(Input::get('uuid'));
        $job->setKeyType($keyType);
        $job->setKeyValue(!empty($keys) ? $keys : [$key]);

        $this->sendKeyCheck($job);
        return response()->json([
            'state' => 'success'
        ] + $this->generateResultBase($job), 200);
    }

    /**
     * File upload endpoint
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fileUpload(Request $request)
    {
        $this->validate($request, [
            'file' => 'bail|required|file|max:1000',
            'uuid'  => 'min:24|max:128'
        ]);

        $file = $request->file('file');
        try {
            $content = File::get($file);

            $job = $this->newTest(Input::get('uuid'));
            $job->setKeyType('file');
            $job->setKeyValue(base64_encode($content));
            $job->setKeyName($file->getClientOriginalName());

            $this->sendKeyCheck($job);
            return response()->json([
                    'state' => 'success'
                ] + $this->generateResultBase($job), 200);

        } catch (Exception $e) {
            return response()->json(['state' => 'not-found'], 404);
        }
    }

    /**
     * File upload endpoint - API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fileUploadApi(Request $request)
    {
        $file = Input::get('file');
        Log::info($file);

        $job = $this->newTest(Input::get('uuid'));
        $job->setKeyType('file');
        $job->setKeyValue($file);

        $this->sendKeyCheck($job);
        return response()->json([
            'state' => 'success'
            ] + $this->generateResultBase($job), 200);
    }

    /**
     * Sends key object to redis queue for scanning
     * @param $job
     */
    protected function sendKeyCheck($job)
    {
        dispatch((new TesterJob($job))->onQueue('tester'));
    }

    /**
     * PGP key / email check
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pgpCheck(Request $request)
    {
        $this->validate($request, [
            'pgp' => 'bail|required|max:128',
            'uuid'  => 'min:24|max:128'
        ]);

        $pgp = Input::get('pgp');

        $job = $this->newTest(Input::get('uuid'));
        $job->setKeyType('pgp');
        $job->setPgp($pgp);

        $this->sendKeyCheck($job);
        return response()->json([
            'state' => 'success'
        ] + $this->generateResultBase($job), 200);
    }

    /**
     * New testing object
     * @param string|null $uuid
     * @return KeyToTest
     */
    protected function newTest($uuid = null)
    {
        return new KeyToTest(empty($uuid) ? Uuid::generate()->string : $uuid);
    }

    /**
     * Response base object
     * @param KeyToTest $obj
     * @return array
     */
    protected function generateResultBase($obj){
        return [
            'uuid' => $obj->getId(),
        ];
    }


}