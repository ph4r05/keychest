<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Keychest\Services\ServerManager;
use App\Models\WatchTarget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
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
     * @var ServerManager
     */
    protected $serverManager;

    /**
     * Create a new controller instance.
     * @param ServerManager $serverManager
     */
    public function __construct(ServerManager $serverManager)
    {
        $this->serverManager = $serverManager;
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
        $parsed = parse_url($server);
        if (empty($parsed) || strpos($server, '.') === false){
            return response()->json(['status' => 'fail'], 422);
        }

        // DB Job data
        $curUser = Auth::user();
        $newJobDb = [
            'scan_connect' => 0,
            'created_at' => Carbon::now(),
            'user_id' => $curUser->getAuthIdentifier()
        ];

        $criteria = $this->serverManager->buildCriteria($parsed, $server);

        // Duplicity detection
        if ($this->serverManager->getHostsBy($criteria, $curUser->getAuthIdentifier())->isNotEmpty()){
            return response()->json(['status' => 'already-present'], 410);
        }

        $newJobDb = array_merge($newJobDb, $criteria);
        $elDb = WatchTarget::create($newJobDb);
        return response()->json(['status' => 'success', 'server' => $newJobDb], 200);
    }

    /**
     * Delete the server
     */
    public function del(){
        $id = intval(Input::get('id'));
        if (empty($id)){
            return response()->json([], 500);
        }

        $curUser = Auth::user();
        $deletedRows = WatchTarget::where('user_id', $curUser->getAuthIdentifier())->where('id', $id)->delete();
        if ($deletedRows) {
            return response()->json(['status' => 'success'], 200);
        } else {
            return response()->json(['status' => 'not-deleted'], 422);
        }
    }

    /**
     * Updates the server watcher record.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(){
        $id = intval(Input::get('id'));
        $server = strtolower(trim(Input::get('server')));
        $parsed = parse_url($server);

        if (empty($id) || empty($parsed) || strpos($server, '.') === false){
            return response()->json(['status' => 'invalid-domain'], 422);
        }

        $curUser = Auth::user();
        $ent = WatchTarget::query()
            ->where('user_id', $curUser->getAuthIdentifier())
            ->where('id', $id)
            ->first();
        if (empty($ent)){
            return response()->json(['status' => 'not-found'], 404);
        }

        $criteria = $this->serverManager->buildCriteria($parsed, $server);

        // Duplicity detection
        $duplicates = $this->serverManager->getHostsBy($criteria, $curUser->getAuthIdentifier());
        if ($duplicates->isNotEmpty() && ($duplicates->first()->id != $ent->id || $duplicates->count() > 1)){
            return response()->json(['status' => 'already-present'], 410);
        }

        // Update
        foreach ($criteria as $key => $val){
            $ent->$key = $val;
        }

        $ent->save();
        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Checks if the host can be added to the certificate monitor
     */
    public function canAddHost(){
        $server = strtolower(trim(Input::get('server')));
        $canAdd = $this->serverManager->canAddHost($server, Auth::user());
        if ($canAdd == -1){
            return response()->json(['status' => 'fail'], 422);
        } elseif ($canAdd == 0){
            return response()->json(['status' => 'already-present'], 410);
        } elseif ($canAdd == 1) {
            return response()->json(['status' => 'success'], 200);
        } else {
            return response()->json(['status' => 'unrecognized-error', 'code' => $canAdd], 500);
        }
    }
}
