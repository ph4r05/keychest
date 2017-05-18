<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 16.05.17
 * Time: 17:39
 */
namespace App\Http\Controllers;

use App\Jobs\ScanHostJob;
use App\ScanJob;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Webpatser\Uuid\Uuid;

class SearchController extends Controller
{
    // use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * IndexController constructor.
     */
    public function __construct()
    {

    }

    /**
     * Show the main index page
     *
     * @return Response
     */
    public function show()
    {
        $data = [];
        return view('index', $data);
    }

    /**
     * Performs the search / check
     *
     * @return Response
     */
    public function search()
    {
        $uuid = Uuid::generate()->string;
        $server = Input::get('scan-target');
        Log::info(sprintf('UUID: %s, target: %s', $uuid, $server));

        $parsed = parse_url($server);
        if (empty($parsed)){
            return view('index', []);
        }

        // DB Job data
        $newJobDb = [
            'id' => $uuid,
            'scan_scheme' => $parsed['scheme'],
            'scan_host' => $parsed['host'],
            'scan_port' => $parsed['port'],
        ];

        $curUser = Auth::user();
        if (!empty($curUser)){
            $newJobDb['user'] = $curUser;
        }

        $elDb = ScanJob::create($newJobDb);
        Log::info(var_export($elDb));

        // Queue entry to the scanner queue
        dispatch((new ScanHostJob($elDb))->onQueue('scanner'));

        $data = ['job_id' => $uuid];
        return view('index', $data);
    }

}

