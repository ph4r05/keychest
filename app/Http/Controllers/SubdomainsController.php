<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Jobs\AutoAddSubsJob;
use App\Keychest\Services\ServerManager;
use App\Keychest\Services\SubdomainManager;
use App\Keychest\Utils\DataTools;
use App\Keychest\Utils\DbTools;
use App\Keychest\Utils\DomainTools;
use App\Models\LastScanCache;
use App\Models\SubdomainResults;
use App\Models\SubdomainWatchAssoc;
use App\Models\SubdomainWatchTarget;


use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\JoinClause;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;

/**
 * Class SubdomainsController
 * @package App\Http\Controllers
 */
class SubdomainsController extends Controller
{
    /**
     * @var SubdomainManager
     */
    protected $manager;

    /**
     * @var ServerManager
     */
    protected $serverManager;

    /**
     * Create a new controller instance.
     * @param SubdomainManager $manager
     * @param ServerManager $serverManager
     */
    public function __construct(SubdomainManager $manager, ServerManager $serverManager)
    {
        $this->manager = $manager;
        $this->serverManager = $serverManager;
        $this->middleware('auth');
    }

    /**
     * Returns list of the subdomain watches
     */
    public function getList()
    {
        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();

        $sort = strtolower(trim(Input::get('sort')));
        $filter = strtolower(trim(Input::get('filter')));
        $per_page = intval(trim(Input::get('per_page', 50)));
        $unfinished = intval(trim(Input::get('unfinished', 0)));
        $sort_parsed = DataTools::vueSortToDb($sort);

        $watchAssocTbl = SubdomainWatchAssoc::TABLE;
        $query = $this->baseLoadQuery($userId);
        if (!empty($filter)){
            $query = $query->where('scan_host', 'like', '%' . $filter . '%');
        }

        // sorting
        $sort_parsed->transform(function($item, $key) use ($watchAssocTbl){
            return (!in_array($item[0], ['created_at', 'updated_at'])) ?
                $item : [$watchAssocTbl.'.'.$item[0], $item[1]];
        });

        $query = DbTools::sortQuery($query, $sort_parsed);
        $ret = $query->paginate($per_page > 0  && $per_page < 1000 ? $per_page : 100);

        $retArr = $ret->toArray();
        $retArr['data'] = $this->processListResults(collect($retArr['data']), true, $unfinished);
        return response()->json($retArr, 200);
    }

    /**
     * Returns only given domain records + loaded subdomains
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDomains(){
        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();

        $domains = collect(Input::get('domains'));

        $watchTbl = SubdomainWatchTarget::TABLE;
        $query = $this->baseLoadQuery($userId);
        $query = $query->whereIn($watchTbl.'.id', $domains);
        $res = $query->get();
        Log::info(var_export($res->all(), true));

        $res = $this->processListResults($res, false);
        return response()->json(['status' => 'success', 'res' => $res, 'ids' => $domains], 200);
    }

    /**
     * Loads list of domains with no results yet.
     * Over all domain records, ignoring pagination, sort & so on.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnfinishedDomains(){
        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();

        $query = $this->baseLoadQuery($userId);
        $query = $query->whereNull('rs.result_size');
        $res = $query->get();

        $res = $this->processListResults($res, false);
        return response()->json(['status' => 'success', 'res' => $res], 200);
    }

    /**
     * Discovered subdomains, annotated with existing user hosts
     */
    public function getDiscoveredSubdomainsList(){
        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();

        $watchTbl = SubdomainWatchTarget::TABLE;
        $watchAssocTbl = SubdomainWatchAssoc::TABLE;
        $watchResTbl = SubdomainResults::TABLE;

        $q = SubdomainResults::query()
            ->join($watchTbl, $watchTbl.'.id', '=', $watchResTbl.'.watch_id')
            ->join($watchAssocTbl, $watchAssocTbl.'.watch_id', '=', $watchTbl.'.id')
            ->where($watchAssocTbl.'.user_id', '=', $userId)
            ->whereNull($watchAssocTbl.'.deleted_at');

        $allHosts = collect();
        $subRes = $q->get();

        $subRes->transform(function($val, $key) use ($allHosts){
            $val->result = json_decode($val->result);
            DataTools::addAll($allHosts, collect($val->result));
            return $val;
        });

        $allHosts = $allHosts->unique()->reject(function($value, $key){
            return DomainTools::isWildcard($value);
        })->values();

        // load all existing hosts
        $allUserHosts = $this->serverManager->getUserHostsQuery($userId)->get();
        $allUserHostNames = $allUserHosts->pluck('scan_host')->unique();
        $allUserHostNamesMap = $allUserHostNames->flip();

        $hostRecords = $allHosts->map(function ($item, $key) use($allUserHostNamesMap) {
            $co = new \stdClass();
            $co->name = $item;
            $co->used = $allUserHostNamesMap->has($item);
            return $co;
        });

        $response = [
            'status' => 'success',
            'subs' => $hostRecords,
            'userHosts' => $allUserHostNames->values()->all(),
        ];
        return response()->json($response, 200);
    }

    /**
     * Adds a new server
     *
     * @return Response
     */
    public function add()
    {
        $server = strtolower(trim(Input::get('server')));
        $autoFill = boolval(trim(Input::get('autoFill')));
        $server = DomainTools::normalizeUserDomainInput($server);

        $maxHosts = config('keychest.max_active_domains');
        if ($maxHosts){
            $numHosts = $this->manager->numDomainsUsed(Auth::user()->getAuthIdentifier());
            if ($numHosts >= $maxHosts) {
                return response()->json(['status' => 'too-many', 'max_limit' => $maxHosts], 429);
            }
        }

        $ret = $this->addSubdomain($server, $autoFill);
        if (is_numeric($ret)){
            if ($ret === -1){
                return response()->json(['status' => 'fail'], 422);
            } elseif ($ret === -2){
                return response()->json(['status' => 'already-present'], 410);
            } elseif ($ret === -3){
                return response()->json(['status' => 'too-many', 'max_limit' => $maxHosts], 429);
            } elseif ($ret === -4){
                return response()->json(['status' => 'blacklisted'], 450);
            } else {
                return response()->json(['status' => 'unknown-fail'], 500);
            }
        } else {
            return response()->json(['status' => 'success', 'server' => $ret], 200);
        }
    }

    /**
     * Adds a new domain - multiple
     *
     * @return Response
     */
    public function addMore()
    {
        $maxHosts = config('keychest.max_active_domains');
        $numHosts = $this->serverManager->numHostsUsed(Auth::user()->getAuthIdentifier());
        if ($maxHosts && $numHosts >= $maxHosts){
            return response()->json(['status' => 'too-many', 'max_limit' => $maxHosts], 429);
        }

        $servers = collect(Input::get('servers'));
        $autoFill = boolval(trim(Input::get('autoFill')));
        $resp = $this->importDomainsArr($servers, $autoFill);
        return response()->json($resp, 200);
    }

    /**
     * Returns set of existing hosts that are suffices of the given host.
     * e.g. existing host keychest.net matches input test.keychest.net
     * @return \Illuminate\Http\JsonResponse
     */
    public function watchingDomainWithSuffix(){
        $host = trim(Input::get('host'));

        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();

        $enabledHosts = $this->manager->getHostsWithInvSuffix($host, $userId, true, true)->get();
        if ($enabledHosts->isNotEmpty()){
            return response()->json([
                'status' => 'existing',
                'enabled' => $enabledHosts
            ], 200);
        }

        return response()->json(['status' => 'free'], 200);
    }

    /**
     * Helper subdomain add function.
     * Used for individual addition and import.
     * @param $server
     * @param $autoFill
     * @return array|int
     */
    protected function addSubdomain($server, $autoFill){
        $parsed = parse_url($server);
        if (empty($parsed) || !DomainTools::isValidParsedUrlHostname($parsed)){
            return -1; //response()->json(['status' => 'fail'], 422);
        } else if ($this->manager->isBlacklisted($parsed['host'])){
            return -4; //response()->json(['status' => 'blacklisted'], 450);
        }

        // DB Job data
        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();
        $newServerDb = [
            'created_at' => Carbon::now(),
        ];

        $criteria = $this->manager->buildCriteria($parsed, $server);
        $newServerDb = array_merge($newServerDb, $criteria);

        // TODO: update criteria with test type
        // ...

        // Duplicity detection, soft delete manipulation
        $hosts = $this->manager->getAllHostsBy($criteria);   // load all matching host records
        $hostAssoc = $this->manager->getHostAssociations($userId, $hosts->pluck('id'));
        $userHosts = $this->manager->filterHostsWithAssoc($hosts, $hostAssoc);

        if ($this->manager->allHostsEnabled($userHosts)){
            return -2; //response()->json(['status' => 'already-present'], 410);
        }

        // Empty hosts - create new bare record
        $hostRecord = $hosts->first();
        if (empty($hostRecord)){
            $hostRecord = SubdomainWatchTarget::create($newServerDb);
        }

        // Association not present -> create a new one
        $assoc = null;
        if ($userHosts->isEmpty()) {
            $assocInfo = [
                'user_id' => $userId,
                'watch_id' => $hostRecord->id,
                'auto_fill_watches' => $autoFill
            ];

            $assoc = SubdomainWatchAssoc::create($assocInfo);
            $newServerDb['assoc'] = $assoc;

        } else {
            $assoc = $hostAssoc->first();
            $assoc->deleted_at = null;
            $assoc->auto_fill_watches = $autoFill;
            $assoc->save();
            $newServerDb['assoc'] = $assoc;
        }

        if ($autoFill){
            $this->autoAddCheck($assoc->id);
        }

        return $newServerDb; //response()->json(['status' => 'success', 'server' => $newServerDb], 200);
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

        $assoc = SubdomainWatchAssoc::where('id', $id)->where('user_id', $userId)->get()->first();
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
     * Delete multiple server association
     * @return \Illuminate\Http\JsonResponse
     */
    public function delMore(){
        $ids = collect(Input::get('ids'));
        if (empty($ids)){
            return response()->json([], 500);
        }

        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();

        $affected = SubdomainWatchAssoc
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
        $autoFill = boolval(trim(Input::get('autoFill')));
        $server = strtolower(trim(Input::get('server')));
        $server = DomainTools::normalizeUserDomainInput($server);
        $parsed = parse_url($server);

        if (empty($id) || empty($parsed) || !DomainTools::isValidParsedUrlHostname($parsed)){
            return response()->json(['status' => 'invalid-domain'], 422);
        } else if ($this->manager->isBlacklisted($parsed['host'])){
            return response()->json(['status' => 'blacklisted'], 450);
        }

        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();

        $curAssoc = SubdomainWatchAssoc::query()->where('id', $id)->where('user_id', $userId)->first();
        if (empty($curAssoc)){
            return response()->json(['status' => 'not-found'], 404);
        }

        $curHost = SubdomainWatchTarget::query()->where('id', $curAssoc->watch_id)->first();
        $oldUrl = DomainTools::assembleUrl('https', $curHost->scan_host, 443);
        $newUrl = DomainTools::normalizeUrl($server);
        if ($oldUrl == $newUrl){
            if ($curAssoc->auto_fill_watches != $autoFill) {
                $curAssoc->updated_at = Carbon::now();
                $curAssoc->auto_fill_watches = $autoFill;
                $curAssoc->save();

                if ($autoFill){
                    $this->autoAddCheck($curAssoc->id);
                }

                return response()->json(['status' => 'success', 'message' => 'updated'], 200);
            }
            return response()->json(['status' => 'success', 'message' => 'nothing-changed'], 200);
        }

        $parsedNew = parse_url($newUrl);
        $criteriaNew = $this->manager->buildCriteria($parsedNew);

        // Duplicity detection, host might be already monitored in a different association record.
        $newHosts = $this->manager->getAllHostsBy($criteriaNew);   // load all matching host records
        $hostNewAssoc = $this->manager->getHostAssociations($userId, $newHosts->pluck('id'));
        $userNewHosts = $this->manager->filterHostsWithAssoc($newHosts, $hostNewAssoc);
        if ($this->manager->allHostsEnabled($userNewHosts)){
            return response()->json(['status' => 'already-present'], 410);
        }

        // Invalidate the old association - delete equivalent
        $curAssoc->deleted_at = Carbon::now();
        $curAssoc->updated_at = Carbon::now();
        $curAssoc->save();

        // Is there some association with the new url already present but disabled? Enable it then
        $assoc = null;
        $newHost = $newHosts->first();
        if (!empty($newHost) && $userNewHosts->isNotEmpty()){
            $assoc = $hostNewAssoc->first();
            $assoc->deleted_at = null;
            $assoc->updated_at = Carbon::now();
            $assoc->auto_fill_watches = $autoFill;
            $assoc->save();

        } else {
            // Try to fetch new target record or create a new one.
            if (empty($newHost)){
                $newServerDb = array_merge(['created_at' => Carbon::now()], $criteriaNew);
                $newHost = SubdomainWatchTarget::create($newServerDb);
            }

            // New association record
            $assocInfo = [
                'user_id' => $userId,
                'watch_id' => $newHost->id,
                'created_at' => Carbon::now(),
                'auto_fill_watches' => $autoFill
            ];

            $assoc = SubdomainWatchAssoc::create($assocInfo);
        }

        if ($autoFill){
            $this->autoAddCheck($assoc->id);
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Checks if the host can be added to the certificate monitor
     */
    public function canAdd(){
        $server = strtolower(trim(Input::get('server')));
        $server = DomainTools::normalizeUserDomainInput($server);
        $canAdd = $this->manager->canAdd($server, Auth::user());
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
     * Submits job to check for auto-add once user changes this setting.
     * @param $assocId
     */
    protected function autoAddCheck($assocId){
        // Queue entry to the scanner queue
        $jobData = ['id' => $assocId];
        dispatch((new AutoAddSubsJob($jobData))->onQueue('scanner'));
    }

    /**
     * Returns true if domain is blacklisted.
     * Pass already normalized URLs here.
     * @param $url
     * @return bool
     */
    protected function isBlacklisted($url){
        $parsed = parse_url($url);
        return $this->manager->isBlacklisted($parsed['host']);
    }

    /**
     * Imports list of servers.
     */
    public function importDomains(){
        $servers = strtolower(trim(Input::get('data')));
        $servers = collect(explode("\n", $servers));
        $autoFill = boolval(trim(Input::get('autoFill')));
        $resp = $this->importDomainsArr($servers, $autoFill);
        return response()->json($resp, 200);
    }

    /**
     * Imports all domains from the input collection
     * @param Collection $servers
     * @param $autoFill
     * @return array
     */
    protected function importDomainsArr($servers, $autoFill){
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

        $maxHosts = config('keychest.max_active_domains');
        $numHosts = $this->manager->numDomainsUsed(Auth::user()->getAuthIdentifier());
        $num_added = 0;
        $num_present = 0;
        $num_blacklisted = 0;
        $num_failed = 0;
        $num_total = $numHosts;
        $hitMaxLimit = false;

        foreach ($validServers->all() as $cur){
            if ($maxHosts && $num_total >= $maxHosts){
                $hitMaxLimit = true;
                break;
            }

            $ret = $this->addSubdomain($cur, $autoFill);
            if (is_numeric($ret)){
                if ($ret === -1){
                    $num_failed += 1;
                } elseif ($ret === -2){
                    $num_present += 1;
                } elseif ($ret === -4){
                    $num_blacklisted += 1;
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
            'num_blacklisted' => $num_blacklisted,
            'num_skipped' => $servers->count() - $validServers->count(),
            'num_total' => $num_total,
            'hit_max_limit' => $hitMaxLimit,
            'max_limit' => $maxHosts
        ];
    }

    /**
     * Builds query for basic load
     * @param $userId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseLoadQuery($userId){
        $watchTbl = SubdomainWatchTarget::TABLE;
        $watchAssocTbl = SubdomainWatchAssoc::TABLE;
        $watchResTbl = SubdomainResults::TABLE;
        $lastScanTbl = LastScanCache::TABLE;

        $query = SubdomainWatchAssoc::query()
            ->join($watchTbl, $watchTbl.'.id', '=', $watchAssocTbl.'.watch_id')
            ->leftJoin($lastScanTbl . ' AS ls', function(JoinClause $join) use($watchTbl) {
                $join->whereRaw('ls.cache_type = 0')
                    ->whereRaw('ls.scan_type = 6')  // sudomain results
                    ->on('ls.obj_id', '=', $watchTbl.'.id');
            })
            ->leftJoin($watchResTbl. ' AS rs', 'rs.id', '=', 'ls.scan_id')
            ->select($watchTbl.'.*', $watchAssocTbl.'.*', $watchTbl.'.id AS wid',
                'rs.scan_status AS sub_scan_status', 'rs.last_scan_at AS sub_last_scan_at',
                'rs.result_size AS sub_result_size', 'rs.result AS sub_result')
            ->where($watchAssocTbl.'.user_id', '=', $userId)
            ->whereNull($watchAssocTbl.'.deleted_at');
        return $query;
    }

    /**
     * Processes result of the load list - checks the result size.
     * @param Collection $col
     * @param bool $removeSubResult
     * @param int $unfinished
     * @return mixed
     */
    protected function processListResults($col, $removeSubResult=true, $unfinished=0){
        $col = $col->transform(function($value, $key) use ($removeSubResult) {
            try{
                $value['sub_result_size'] = -1;
                if (empty($value['sub_result'])){
                    return $value;
                }

                $value['sub_result'] = json_decode($value['sub_result']);
                $value['sub_result_size'] = count($value['sub_result']);

                // Optimization, not needed in frontend for now.
                if ($removeSubResult) {
                    $value['sub_result'] = [];
                }

                return $value;
            } catch(Exception $e){
                return $value;
            }
        });

        if ($unfinished != 0){
            $fnc = function($value, $key) {
                return empty($value['sub_result']) || $value['sub_result_size'] == -1;
            };

            if ($unfinished == 1){
                $col = $col->filter($fnc);  // take only unfinished
            } else {
                $col = $col->reject($fnc);
            }
        }

        return $col;
    }
}
