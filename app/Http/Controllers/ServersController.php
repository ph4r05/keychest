<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Keychest\Services\ScanManager;
use App\Keychest\Services\ServerManager;
use App\Keychest\Utils\DataTools;
use App\Keychest\Utils\DbTools;
use App\Keychest\Utils\DomainTools;
use App\Models\DnsEntry;
use App\Models\DnsResult;
use App\Models\HandshakeScan;
use App\Models\WatchAssoc;
use App\Models\WatchTarget;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * Scan manager
     * @var ScanManager
     */
    protected $scanManager;

    /**
     * Create a new controller instance.
     * @param ServerManager $serverManager
     * @param ScanManager $scanManager
     */
    public function __construct(ServerManager $serverManager, ScanManager $scanManager)
    {
        $this->serverManager = $serverManager;
        $this->scanManager = $scanManager;
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
        $userId = $curUser->getAuthIdentifier();

        $sort = strtolower(trim(Input::get('sort')));
        $filter = strtolower(trim(Input::get('filter')));
        $per_page = intval(trim(Input::get('per_page')));
        $return_all = intval(trim(Input::get('return_all')));
        $sort_parsed = DataTools::vueSortToDb($sort);

        $watchTbl = (new WatchTarget())->getTable();
        $watchAssocTbl = (new WatchAssoc())->getTable();

        // start watching from registration before server load
        $this->checkStartWatching();

        // server list load
        $query = $this->serverManager->loadServerList($userId);
        if (!empty($filter)){
            $query = $query->where('scan_host', 'like', '%' . $filter . '%');
        }

        // sorting
        $sort_parsed->transform(function($item, $key) use ($watchAssocTbl){
            return (!in_array($item[0], ['created_at', 'updated_at'])) ?
                $item : [$watchAssocTbl.'.'.$item[0], $item[1]];
        });

        $query = DbTools::sortQuery($query, $sort_parsed);

        if ($return_all) {
            $ret = $query->paginate(config('keychest.max_servers'));
        } else {
            $ret = $query->paginate($per_page > 0 && $per_page < 1000 ? $per_page : 100); // type: \Illuminate\Pagination\LengthAwarePaginator
        }
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
        $server = DomainTools::normalizeUserDomainInput($server);

        $maxHosts = config('keychest.max_servers');
        $numHosts = $this->serverManager->numHostsUsed(Auth::user()->getAuthIdentifier());
        if ($maxHosts && $numHosts >= $maxHosts){
            return response()->json(['status' => 'too-many', 'max_limit' => $maxHosts], 429);
        }

        $ret = $this->addServer($server);
        if (is_numeric($ret)){
            if ($ret === -1){
                return response()->json(['status' => 'fail'], 422);
            } elseif ($ret === -2){
                return response()->json(['status' => 'already-present'], 410);
            } elseif ($ret === -3){
                return response()->json(['status' => 'too-many', 'max_limit' => $maxHosts], 429);
            } else {
                return response()->json(['status' => 'unknown-fail'], 500);
            }
        } else {
            return response()->json(['status' => 'success', 'server' => $ret], 200);
        }
    }

    /**
     * Adds a new server - multiple
     *
     * @return Response
     */
    public function addMore()
    {
        $maxHosts = config('keychest.max_servers');
        $numHosts = $this->serverManager->numHostsUsed(Auth::user()->getAuthIdentifier());
        if ($maxHosts && $numHosts >= $maxHosts){
            return response()->json(['status' => 'too-many', 'max_limit' => $maxHosts], 429);
        }

        $servers = collect(Input::get('servers'));
        $resp = $this->importServersArr($servers);
        return response()->json($resp, 200);
    }

    /**
     * Start watching feature
     * Used when unregistered user hits start watching button.
     *
     * Session values used:
     *  - start_watching
     *  - last_scan_url
     */
    protected function checkStartWatching(){
        $sess = request()->session();
        if (!session('start_watching')){
            return -10;
        }

        $sess->forget('start_watching');
        $server = $sess->get('last_scan_url');
        return $this->addServer($server);
    }

    /**
     * Helper server add function.
     * Used for individual addition and import.
     * @param $server
     * @return array|int
     */
    protected function addServer($server)
    {
        $parsed = parse_url($server);
        if (empty($parsed) || !DomainTools::isValidParsedUrlHostname($parsed)){
            return -1;
        }

        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();
        $newServerDb = [
            'created_at' => Carbon::now(),
        ];

        $criteria = $this->serverManager->buildCriteria($parsed, $server);
        $newServerDb = array_merge($newServerDb, $criteria);

        // TODO: update criteria with test type
        // ...

        // Duplicity detection, soft delete manipulation
        $hosts = $this->serverManager->getAllHostsBy($criteria);   // load all matching host records
        $hostAssoc = $this->serverManager->getHostAssociations($userId, $hosts->pluck('id'));
        $userHosts = $this->serverManager->filterHostsWithAssoc($hosts, $hostAssoc);

        if ($this->serverManager->allHostsEnabled($userHosts)){
            return -2;
        }

        // Empty hosts - create new bare record
        $hostRecord = $hosts->first();
        if (empty($hostRecord)){
            $hostRecord = WatchTarget::create($newServerDb);
        }

        // Association not present -> create a new one
        if ($userHosts->isEmpty()) {
            $assocInfo = [
                'user_id' => $userId,
                'watch_id' => $hostRecord->id,
            ];

            $assocDb = WatchAssoc::create($assocInfo);
            $newServerDb['assoc'] = $assocDb;

        } else {
            $assoc = $hostAssoc->first();
            $assoc->deleted_at = null;
            $assoc->save();
            $newServerDb['assoc'] = $assoc;
        }
        return $newServerDb;
    }

    /**
     * Delete the server association
     */
    public function del(){
        $id = intval(Input::get('id'));
        if (empty($id)){
            return response()->json([], 500);
        }

        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();

        $assoc = WatchAssoc::where('id', $id)->where('user_id', $userId)->get()->first();
        if (empty($assoc) || !empty($assoc->deleted_at)){
            return response()->json(['status' => 'not-deleted'], 422);
        } else {
            $assoc->deleted_at = Carbon::now();
            $assoc->updated_at = Carbon::now();
            $assoc->save();
            return response()->json(['status' => 'success'], 200);
        }
    }

    /**
     * Delete the server association
     */
    public function delMore(){
        $ids = collect(Input::get('ids'));
        if (empty($ids)){
            return response()->json([], 500);
        }

        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();


        $affected = WatchAssoc
            ::whereIn('id', $ids->all())
            ->where('user_id', $userId)
            ->update([
                'deleted_at' => Carbon::now(),
                'updated_at' => Carbon::now()
                ]);
        if (empty($affected)){
            return response()->json(['status' => 'not-deleted'], 422);
        }

        return response()->json(['status' => 'success', 'affected' => $affected, 'size' => $ids->count()], 200);
    }

    /**
     * Updates the server watcher record.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(){
        $id = intval(Input::get('id'));
        $server = strtolower(trim(Input::get('server')));
        $server = DomainTools::normalizeUserDomainInput($server);
        $parsed = parse_url($server);

        if (empty($id) || empty($parsed) || !DomainTools::isValidParsedUrlHostname($parsed)){
            return response()->json(['status' => 'invalid-domain'], 422);
        }

        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();

        $curAssoc = WatchAssoc::query()->where('id', $id)->where('user_id', $userId)->first();
        if (empty($curAssoc)){
            return response()->json(['status' => 'not-found'], 404);
        }

        $curHost = WatchTarget::query()->where('id', $curAssoc->watch_id)->first();
        $oldUrl = DomainTools::assembleUrl($curHost->scan_scheme, $curHost->scan_host, $curHost->scan_port);
        $newUrl = DomainTools::normalizeUrl($server);
        if ($oldUrl == $newUrl){
            return response()->json(['status' => 'success', 'message' => 'nothing-changed'], 200);
        }

        $parsedNew = parse_url($newUrl);
        $criteriaNew = $this->serverManager->buildCriteria($parsedNew);

        // Duplicity detection, host might be already monitored in a different association record.
        $newHosts = $this->serverManager->getAllHostsBy($criteriaNew);   // load all matching host records
        $hostNewAssoc = $this->serverManager->getHostAssociations($userId, $newHosts->pluck('id'));
        $userNewHosts = $this->serverManager->filterHostsWithAssoc($newHosts, $hostNewAssoc);
        if ($this->serverManager->allHostsEnabled($userNewHosts)){
            return response()->json(['status' => 'already-present'], 410);
        }

        // Invalidate the old association - delete equivalent
        $curAssoc->deleted_at = Carbon::now();
        $curAssoc->updated_at = Carbon::now();
        $curAssoc->save();

        // Is there some association with the new url already present but disabled? Enable it then
        $newHost = $newHosts->first();
        if (!empty($newHost) && $userNewHosts->isNotEmpty()){
            $assoc = $hostNewAssoc->first();
            $assoc->deleted_at = null;
            $assoc->updated_at = Carbon::now();
            $assoc->save();

        } else {
            // Try to fetch new target record or create a new one.
            if (empty($newHost)){
                $newServerDb = array_merge(['created_at' => Carbon::now()], $criteriaNew);
                $newHost = WatchTarget::create($newServerDb);
            }

            // New association record
            $assocInfo = [
                'user_id' => $userId,
                'watch_id' => $newHost->id,
                'created_at' => Carbon::now()
            ];

            $assocDb = WatchAssoc::create($assocInfo);
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Checks if the host can be added to the certificate monitor
     */
    public function canAddHost(){
        $server = strtolower(trim(Input::get('server')));
        $server = DomainTools::normalizeUserDomainInput($server);
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

    /**
     * Imports list of servers.
     */
    public function importServers(){
        $servers = strtolower(trim(Input::get('data')));
        $servers = collect(explode("\n", $servers));
        $resp = $this->importServersArr($servers);
        return response()->json($resp, 200);
    }

    /**
     * Imports all servers from the input collection
     * @param $servers
     * @return array
     */
    protected function importServersArr($servers){
        $servers = $servers->reject(function($value, $key){
            return empty(trim($value));
        })->values()->take(1000)->unique()->values()->take(100);

        // Domain name sanitizing
        $servers->transform(function($value, $key){
            return DomainTools::normalizeUserDomainInput($value);
        });

        // Kick out invalid ones
        $validServers = $servers->reject(function($value, $key){
            $parsed = parse_url($value);
            return (empty($parsed) || !DomainTools::isValidParsedUrlHostname($parsed));
        })->values();

        $maxHosts = config('keychest.max_servers');
        $numHosts = $this->serverManager->numHostsUsed(Auth::user()->getAuthIdentifier());
        $num_added = 0;
        $num_present = 0;
        $num_failed = 0;
        $num_total = $numHosts;
        $hitMaxLimit = false;

        foreach ($validServers->all() as $cur){
            if ($maxHosts && $num_total >= $maxHosts){
                $hitMaxLimit = true;
                break;
            }

            $ret = $this->addServer($cur);
            if (is_numeric($ret)){
                if ($ret === -1){
                    $num_failed += 1;
                } elseif ($ret === -2){
                    $num_present += 1;
                } else {
                    $num_failed += 1;
                }
            } else {
                $num_added += 1;
                $num_total += 1;
            }
        }

        $outTransformed = $validServers->values()->implode("\n");
        return [
            'status' => 'success',
            'transformed' => $outTransformed,
            'num_hosts' => $numHosts,
            'num_added' => $num_added,
            'num_present' => $num_present,
            'num_failed' => $num_failed,
            'num_skipped' => $servers->count() - $validServers->count(),
            'num_total' => $num_total,
            'hit_max_limit' => $hitMaxLimit,
            'max_limit' => $maxHosts
        ];
    }
}
