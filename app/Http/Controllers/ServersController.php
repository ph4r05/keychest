<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\WatchTarget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;

/**
 * Class ServersController
 * @package App\Http\Controllers
 */
class ServersController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        return view('servers');
    }

    /**
     * Returns list of the servers
     */
    public function getList()
    {
        $curUser = Auth::user();

        $sort = strtolower(trim(Input::get('sort')));
        $filter = strtolower(trim(Input::get('filter')));
        $per_page = intval(trim(Input::get('per_page')));
        $asc = true;

        if (strpos($sort, '|') !== false){
            list($sort, $ordtxt) = explode('|', $sort, 2);
            $asc = $ordtxt == 'asc';
        }

        $query = WatchTarget::query()->where('user_id', $curUser->getAuthIdentifier());

        if (!empty($filter)){
            $query = $query->where('scan_host', 'like', '%' . $filter . '%');
        }

        if (!empty($sort)){
            $query = $query->orderBy($sort, $asc ? 'asc' : 'desc');
        }

        $ret = $query->paginate($per_page > 0  && $per_page < 1000 ? $per_page : 100);
        return response()->json($ret, 200);
    }

    /**
     * Adds a new server
     *
     * @return Response
     */
    public function add()
    {
        $server = strtolower(trim(Input::get('server')));
        Log::info(sprintf('Target: %s', $server));

        $parsed = parse_url($server);
        if (empty($parsed) || strpos($server, '.') === false){
            return response()->json(['status' => 'fail'], 422);
        }

        // DB Job data
        $curUser = Auth::user();
        $newJobDb = [
            'scan_scheme' => isset($parsed['scheme']) ? $parsed['scheme'] : null,
            'scan_host' => isset($parsed['host']) ? $parsed['host'] : $server,
            'scan_port' => isset($parsed['port']) ? $parsed['port'] : null,
            'scan_connect' => 0,
            'created_at' => Carbon::now(),
            'user_id' => $curUser->getAuthIdentifier()
        ];

        $elDb = WatchTarget::create($newJobDb);
        return response()->json(['status' => 'success', 'server' => $newJobDb], 200);
    }
}
