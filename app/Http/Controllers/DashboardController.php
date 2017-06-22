<?php

/*
 * Dashboard controller, loading data.
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\DnsResult;
use App\Models\HandshakeScan;
use App\Models\WatchAssoc;
use App\Models\WatchTarget;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class DashboardController
 * @package App\Http\Controllers
 */
class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function loadActiveCerts()
    {
        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();

        $wq = $this->getActiveWatcher($userId);
        $activeWatches = $wq->get();
        $activeWatchesIds = $activeWatches->pluck('wid');
        Log::info('Active watches ids: ' . var_export($activeWatchesIds->all(), true));

        // Load all newest DNS scans for active watches
        $q = $this->getNewestDnsScans($activeWatchesIds);
        Log::info(var_export($q->toSql(), true));
        $dnsScans = $q->get();
        Log::info(var_export($dnsScans->count(), true));

        // Determine primary IP addresses
        $primaryIPs = $dnsScans->mapWithKeys(function ($item) {
            try{
                $dns = json_decode($item->dns);
                $primary = empty($dns) ? null : $dns[0][1];
                return [intval($item->watch_id) => $primary];
            } catch (Exception $e){
                return [];
            }
        });
        Log::info(var_export($primaryIPs->toJson(), true));

        // Load latest TLS scans for active watchers for primary IP addresses.
        $q = $this->getNewestTlsScans($activeWatchesIds, $dnsScans, $primaryIPs);
        Log::info(var_export($q->toSql(), true));
        $tlsScans = $q->get();
        Log::info(var_export($tlsScans->count(), true));
    }

    /**
     * Returns the newest TLS scans given the watches of interest and loaded DNS scans
     * @param $watches
     * @param $dnsScans
     * @param Collection $primaryIPs
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getNewestTlsScans($watches, $dnsScans, $primaryIPs){
        $table = (new HandshakeScan())->getTable();

        $qq = DnsResult::query()
            ->select(['x.watch_id', 'x.ip_scanned'])
            ->selectRaw('MAX(x.last_scan_at) AS last_scan')
            ->from($table . ' AS x')
            ->whereIn('x.watch_id', $watches)
            ->whereNotNull('x.ip_scanned');

        if ($primaryIPs != null && $primaryIPs->isNotEmpty()){
            $qq = $qq->whereIn('x.ip_scanned',
                $primaryIPs
                    ->values()
                    ->reject(function($item){
                        return empty($item);
                    })
                    ->all());
        }

        $qq = $qq->groupBy('x.watch_id', 'x.ip_scanned');
        $qqSql = $qq->toSql();

        $q = DnsResult::query()
            ->from($table . ' AS s')
            ->join(
                DB::raw('(' . $qqSql. ') AS ss'),
                function(JoinClause $join) use ($qq) {
                    $join->on('s.watch_id', '=', 'ss.watch_id')
                        ->on('s.ip_scanned', '=', 'ss.ip_scanned')
                        ->on('s.last_scan_at', '=', 'ss.last_scan')
                        ->addBinding($qq->getBindings());
                });

        return $q;
    }

    /**
     * Returns a query builder for getting newest DNS results for the given watch array.
     *
     * @param Collection $watches
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getNewestDnsScans($watches){
        // DNS records for given watches.
        // select * from scan_dns s
        // inner join (
        //      select x.watch_id, max(x.last_scan_at) as last_scan
        //      from scan_dns x
        //      WHERE x.watch_id IN (23)
        //      group by x.watch_id ) ss
        // ON s.watch_id = ss.watch_id AND s.last_scan_at = ss.last_scan;
        $dnsTable = (new DnsResult())->getTable();

        $qq = DnsResult::query()
            ->select('x.watch_id')
            ->selectRaw('MAX(x.last_scan_at) AS last_scan')
            ->from($dnsTable . ' AS x')
            ->whereIn('x.watch_id', $watches)
            ->groupBy('x.watch_id');
        $qqSql = $qq->toSql();

        $q = DnsResult::query()
            ->from($dnsTable . ' AS s')
            ->join(
                DB::raw('(' . $qqSql. ') AS ss'),
                function(JoinClause $join) use ($qq) {
                    $join->on('s.watch_id', '=', 'ss.watch_id')
                        ->on('s.last_scan_at', '=', 'ss.last_scan')
                        ->addBinding($qq->getBindings());
                });

        return $q;
    }

    /**
     * Returns the query builder for the active watchers for the user id.
     * @param $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getActiveWatcher($userId){
        $watchTbl = (new WatchTarget())->getTable();
        $watchAssocTbl = (new WatchAssoc())->getTable();

        $query = WatchAssoc::query()
            ->join($watchTbl, $watchTbl.'.id', '=', $watchAssocTbl.'.watch_id')
            ->select($watchTbl.'.*', $watchAssocTbl.'.*', $watchTbl.'.id as wid' )
            ->where($watchAssocTbl.'.user_id', '=', $userId)
            ->whereNull($watchAssocTbl.'.deleted_at');
        return $query;
    }


}