<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Keychest\Services\IpScanManager;
use App\Keychest\Services\ScanManager;
use App\Keychest\Services\ServerManager;
use App\Keychest\Utils\DataTools;
use App\Keychest\Utils\DbTools;
use App\Keychest\Utils\DomainTools;


use App\Models\IpScanRecord;
use App\Models\OwnerIpScanRecord;
use App\Models\OwnerIpScanRecordAssoc;


use Carbon\Carbon;


use Illuminate\Http\Response;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use App\Keychest\Utils\IpRange\InvalidRangeException;
use App\Keychest\Utils\IpRange;
use Mockery\Exception;

/**
 * Class NetworksController
 * @package App\Http\Controllers
 */
class NetworksController extends Controller
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
     * Scan manager
     * @var IpScanManager
     */
    protected $ipScanManager;

    /**
     * Create a new controller instance.
     * @param ServerManager $serverManager
     * @param ScanManager $scanManager
     * @param IpScanManager $ipScanManager
     */
    public function __construct(ServerManager $serverManager, ScanManager $scanManager, IpScanManager $ipScanManager)
    {
        $this->serverManager = $serverManager;
        $this->scanManager = $scanManager;
        $this->ipScanManager = $ipScanManager;
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        return view('ipservers');
    }

    /**
     * Loads all scan definitions for the current user
     * @return \Illuminate\Http\JsonResponse
     */
    public function ipScanList(){
        $curUser = Auth::user();
        $ownerId = $curUser->primary_owner_id;

        $sort = strtolower(trim(Input::get('sort')));
        $filter = strtolower(trim(Input::get('filter')));
        $per_page = intval(trim(Input::get('per_page')));
        $sort_parsed = DataTools::vueSortToDb($sort);
        $watchAssocTbl = OwnerIpScanRecord::TABLE;

        $query = $this->ipScanManager->getRecords($ownerId);
        if (!empty($filter)){
            $query = $query->where('service_name', 'like', '%' . $filter . '%');
        }

        // sorting
        $sort_parsed->transform(function($item, $key) use ($watchAssocTbl){
            return (!in_array($item[0], ['created_at', 'updated_at'])) ?
                $item : [$watchAssocTbl.'.'.$item[0], $item[1]];
        });

        $query = DbTools::sortQuery($query, $sort_parsed);

        $ret = $query->paginate($per_page > 0 && $per_page < 1000 ? $per_page : 100); // type: \Illuminate\Pagination\LengthAwarePaginator
        return response()->json($ret, 200);
    }

    /**
     * Adds a new IP scan range
     *
     * @return Response
     */
    public function add()
    {
        $server = strtolower(trim(Input::get('server')));
        $server = DomainTools::normalizeUserDomainInput($server);
        $range = trim(Input::get('scan_range'));

        $maxHosts = config('keychest.max_scan_records');
        if ($maxHosts){
            $numHosts = $this->ipScanManager->numRecordsUsed(Auth::user()->primary_owner_id);
            if ($numHosts >= $maxHosts) {
                return response()->json(['status' => 'too-many', 'max_limit' => $maxHosts], 429);
            }
        }

        try {
            $ret = $this->addScanRecord($server, $range);
            if (is_numeric($ret)){
                if ($ret === -1){
                    return response()->json(['status' => 'fail'], 422);
                } elseif ($ret === -2){
                    return response()->json(['status' => 'already-present'], 410);
                } elseif ($ret === -3){
                    return response()->json(['status' => 'too-many', 'max_limit' => $maxHosts], 429);
                } elseif ($ret === -4){
                    return response()->json(['status' => 'blacklisted'], 450);
                } elseif ($ret === -5){
                    return response()->json(['status' => 'range-too-big'], 452);
                } elseif ($ret === -6){
                    return response()->json(['status' => 'range-overlap'], 453);
                } else {
                    return response()->json(['status' => 'unknown-fail'], 500);
                }
            } else {
                return response()->json(['status' => 'success', 'record' => $ret], 200);
            }

        } catch(InvalidRangeException $e){
            Log::debug('Invalid range: '.$range);
            return response()->json(['status' => 'invalid-range'], 451);
        } catch(Exception $e){
            Log::error($e);
            return response()->json(['status' => 'exception'], 500);
        }
    }

    /**
     * Delete the scan record association
     * @return \Illuminate\Http\JsonResponse
     */
    public function del(){
        $id = intval(Input::get('id'));
        if (empty($id)){
            return response()->json([], 500);
        }

        $curUser = Auth::user();
        $ownerId = $curUser->primary_owner_id;

        $assoc = OwnerIpScanRecordAssoc
            ::where('id', $id)
            ->where('owner_id', $ownerId)
            ->get()->first();

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
     * Delete multiple scan records association
     * @return \Illuminate\Http\JsonResponse
     */
    public function delMore(){
        $ids = collect(Input::get('ids'));
        if (empty($ids)){
            return response()->json([], 500);
        }

        $curUser = Auth::user();
        $ownerId = $curUser->primary_owner_id;

        $affected = OwnerIpScanRecordAssoc
            ::whereIn('id', $ids->all())
            ->where('owner_id', $ownerId)
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
     * Updates the scan record.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(){
        $id = intval(Input::get('id'));
        $range = trim(Input::get('scan_range'));
        $server = strtolower(trim(Input::get('server')));
        $server = DomainTools::normalizeUserDomainInput($server);
        $parsed = parse_url($server);

        if (empty($id) || empty($parsed) || !DomainTools::isValidParsedUrlHostname($parsed)){
            return response()->json(['status' => 'invalid-domain'], 422);
        }

        $curUser = Auth::user();
        $ownerId = $curUser->primary_owner_id;

        // Load existing association being edited - will be modified, has to exist.
        $curAssoc = OwnerIpScanRecordAssoc::query()
            ->where('id', $id)
            ->where('owner_id', $ownerId)
            ->first();

        if (empty($curAssoc)){
            return response()->json(['status' => 'not-found'], 404);
        }

        $rangeObj = null;
        try {
            $rangeObj = IpRange::rangeFromString($range);
        } catch (InvalidRangeException $e){
            return response()->json(['status' => 'invalid-range'], 451);
        }

        $criteria = IpScanManager::ipScanRangeCriteria(
            $parsed['host'],
            $parsed['port'],
            $rangeObj->getStartAddress(),
            $rangeObj->getEndAddress());

        // TODO: port
        $curRecord = IpScanRecord::query()->where('id', $curAssoc->ip_scan_record_id)->first();
        $curCriteria = IpScanManager::ipScanRangeCriteria(
            $curRecord->service_name,
            $curRecord->service_port,
            $curRecord->ip_beg,
            $curRecord->ip_end);

        if ($criteria === $curCriteria){
            return response()->json(['status' => 'success', 'message' => 'nothing-changed'], 200);
        }

        // Delete & add new.
        // Additional check - is not new edited record already present?
        try {
            $ret = $this->addScanRecord($server, $rangeObj, [$curAssoc->ip_scan_record_id]);
            if (is_numeric($ret)){
                if ($ret === -1){
                    return response()->json(['status' => 'fail'], 422);
                } elseif ($ret === -2){
                    return response()->json(['status' => 'already-present'], 410);
                } elseif ($ret === -5){
                    return response()->json(['status' => 'range-too-big'], 452);
                } elseif ($ret === -6){
                    return response()->json(['status' => 'range-overlap'], 453);
                } else {
                    return response()->json(['status' => 'unknown-fail'], 500);
                }
            }

            // Invalidate the old association - delete equivalent
            $curAssoc->deleted_at = Carbon::now();
            $curAssoc->updated_at = Carbon::now();
            $curAssoc->save();

        } catch(Exception $e){
            Log::error($e);
            return response()->json(['status' => 'exception'], 500);
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Helper scan record add function.
     * Used for individual addition and import.
     * @param $server
     * @param $range
     * @param array $ignoreOverlaps
     * @return array|int
     */
    protected function addScanRecord($server, $range, $ignoreOverlaps=[]){
        $parsed = parse_url($server);
        if (empty($parsed) || !DomainTools::isValidParsedUrlHostname($parsed)){
            return -1;
        }

        $rangeObj = ($range instanceof IpRange) ? $range : IpRange::rangeFromString($range);
        $maxRange = config('keychest.max_scan_range');
        if ($maxRange && $rangeObj->getSize() > $maxRange){
            return -5;
        }

        $criteria = IpScanManager::ipScanRangeCriteria(
            $parsed['host'],
            $parsed['port'],
            $rangeObj->getStartAddress(),
            $rangeObj->getEndAddress());

        // DB Job data
        $curUser = Auth::user();
        $ownerId = $curUser->primary_owner_id;

        // Already covered?
        $overlaps = $this->ipScanManager->hasScanIntersection($criteria, $ownerId, false);
        $overlaps = $overlaps->pluck('record_id')->whereNotIn(null, $ignoreOverlaps);
        if ($overlaps->isNotEmpty()){
            return -6;
        }

        // Fetch or create
        list($scanRecord, $isNew) = $this->ipScanManager->fetchOrCreateRecord($criteria,
            function ($data) use ($rangeObj) {
                $data['ip_beg_int'] = DomainTools::ipv4ToIdx($data['ip_beg']);
                $data['ip_end_int'] = DomainTools::ipv4ToIdx($data['ip_end']);
                return $data;
        });

        if(!$isNew){
            // Soft delete manipulation - fetch user association and reassoc if exists
            $assoc = OwnerIpScanRecordAssoc::query()
                ->where('owner_id', '=', $ownerId)
                ->where('ip_scan_record_id', '=', $scanRecord->id)
                ->withTrashed()
                ->first();

            if ($assoc && $assoc->deleted_at == null && $assoc->disabled_at == null){
                return -2;  // already there
            }

            if ($assoc){
                $assoc->deleted_at = null;
                $assoc->disabled_at = null;
                $assoc->save();
                return $scanRecord;
            }
        }

        // Creating new association.
        $scanRecord->users()->attach($ownerId, [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'auto_fill_watches' => 1,
            ]);

        return $scanRecord;  //response()->json(['status' => 'success', 'server' => $newServerDb], 200);
    }



}
