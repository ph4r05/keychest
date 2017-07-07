<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Keychest\Services\ServerManager;
use App\Keychest\Services\SubdomainManager;
use App\Keychest\Utils\DataTools;
use App\Keychest\Utils\DbTools;
use App\Keychest\Utils\DomainTools;
use App\Models\SubdomainResults;
use App\Models\SubdomainWatchAssoc;
use App\Models\SubdomainWatchTarget;
use App\Models\WatchAssoc;
use App\Models\WatchTarget;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        $per_page = intval(trim(Input::get('per_page')));
        $sort_parsed = DataTools::vueSortToDb($sort);

        $watchTbl = (new SubdomainWatchTarget())->getTable();
        $watchAssocTbl = (new SubdomainWatchAssoc())->getTable();

        $query = SubdomainWatchAssoc::query()
            ->join($watchTbl, $watchTbl.'.id', '=', $watchAssocTbl.'.watch_id')
            ->select($watchTbl.'.*', $watchAssocTbl.'.*')
            ->where($watchAssocTbl.'.user_id', '=', $userId)
            ->whereNull($watchAssocTbl.'.deleted_at');

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
        return response()->json($ret, 200);
    }

    /**
     * Discovered subdomains, annotated with existing user hosts
     */
    public function getDiscoveredSubdomainsList(){
        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();

        $watchTbl = (new SubdomainWatchTarget())->getTable();
        $watchAssocTbl = (new SubdomainWatchAssoc())->getTable();
        $watchResTbl = (new SubdomainResults())->getTable();

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
        $server = DomainTools::normalizeUserDomainInput($server);
        $parsed = parse_url($server);
        if (empty($parsed) || !DomainTools::isValidParsedUrlHostname($parsed)){
            return response()->json(['status' => 'fail'], 422);
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
            return response()->json(['status' => 'already-present'], 410);
        }

        // Empty hosts - create new bare record
        $hostRecord = $hosts->first();
        if (empty($hostRecord)){
            $hostRecord = SubdomainWatchTarget::create($newServerDb);
        }

        // Association not present -> create a new one
        if ($userHosts->isEmpty()) {
            $assocInfo = [
                'user_id' => $userId,
                'watch_id' => $hostRecord->id,
            ];

            $assocDb = SubdomainWatchAssoc::create($assocInfo);
            $newServerDb['assoc'] = $assocDb;

        } else {
            $assoc = $hostAssoc->first();
            $assoc->deleted_at = null;
            $assoc->save();
            $newServerDb['assoc'] = $assoc;
        }

        return response()->json(['status' => 'success', 'server' => $newServerDb], 200);
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

        $curAssoc = SubdomainWatchAssoc::query()->where('id', $id)->where('user_id', $userId)->first();
        if (empty($curAssoc)){
            return response()->json(['status' => 'not-found'], 404);
        }

        $curHost = SubdomainWatchTarget::query()->where('id', $curAssoc->watch_id)->first();
        $oldUrl = DomainTools::assembleUrl('https', $curHost->scan_host, 443);
        $newUrl = DomainTools::normalizeUrl($server);
        if ($oldUrl == $newUrl){
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
                $newHost = SubdomainWatchTarget::create($newServerDb);
            }

            // New association record
            $assocInfo = [
                'user_id' => $userId,
                'watch_id' => $newHost->id,
                'created_at' => Carbon::now()
            ];

            $assocDb = SubdomainWatchAssoc::create($assocInfo);
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
}
