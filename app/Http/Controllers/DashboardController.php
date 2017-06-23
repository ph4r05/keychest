<?php

/*
 * Dashboard controller, loading data.
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Keychest\Utils\DomainTools;
use App\Models\BaseDomain;
use App\Models\Certificate;
use App\Models\CrtShQuery;
use App\Models\DnsResult;
use App\Models\HandshakeScan;
use App\Models\WatchAssoc;
use App\Models\WatchTarget;
use App\Models\WhoisResult;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class DashboardController
 * TODO: move logic to the manager, this will be needed also for reporting (scheduled emails)
 *
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
        $dnsScans = $this->processDnsScans($q->get());
        Log::info(var_export($dnsScans->count(), true));

        // Determine primary IP addresses
        $primaryIPs = $this->getPrimaryIPs($dnsScans);
        Log::info(var_export($primaryIPs->toJson(), true));

        // Load latest TLS scans for active watchers for primary IP addresses.
        $q = $this->getNewestTlsScans($activeWatchesIds, $dnsScans, $primaryIPs);
        $tlsScans = $this->processTlsScans($q->get());
        Log::info(var_export($tlsScans->count(), true));

        // Latest CRTsh scan
        $crtshScans = $this->getNewestCrtshScans($activeWatchesIds)->get();
        $crtshScans = $this->processCrtshScans($crtshScans);
        Log::info(var_export($crtshScans->count(), true));

        // Certificate IDs from TLS scans - more important certs.
        // Load also crtsh certificates.
        $tlsCertsIds = $tlsScans->map(function($item, $key){
            return $item->cert_id_leaf;
        })->reject(function($item){
            return empty($item);
        })->unique();

        $crtshCertIds = $crtshScans->reduce(function($carry, $item){
            return $carry->union(collect($item->certs_ids));
        }, collect())->unique()->sort()->reverse()->take(100);

        $certsToLoad = $tlsCertsIds->union($crtshCertIds)->unique()->values();
        $certs = $this->loadCertificates($certsToLoad)->get();
        $certs->transform(function ($item, $key) use ($tlsCertsIds, $certsToLoad) {
            $this->attributeCertificate($item, $tlsCertsIds->values(), 'found_tls_scan');
            $this->attributeCertificate($item, $certsToLoad->values(), 'found_crt_sh');
            $this->augmentCertificate($item);
            return $item;
        });
        Log::info(var_export($certs->count(), true));

        // TODO: downtime computation?
        // TODO: CAs?
        // TODO: self signed?
        // TODO: custom PEM certs?
        // TODO: %. crtsh / CT wildcard search?
        // TODO: wildcard scan search - from neighbourhood

        // Search based on crt.sh search.
        $data = [
            'status' => 'success',
            'watches' => $activeWatches->all(),
            'wids' => $activeWatchesIds->all(),
            'dns' => $dnsScans,
            'primary_ip' => $primaryIPs,
            'certificates' => $certs

        ];

        return response()->json($data, 200);
    }

    /**
     * @param $certificate
     * @param \Illuminate\Support\Collection $idset
     * @param string $val
     */
    protected function attributeCertificate($certificate, $idset, $val)
    {
        $certificate->$val = $idset->contains($certificate->id);
        return $certificate;
    }

    /**
     * Extends certificate record
     * @param $certificate
     * @return mixed
     */
    protected function augmentCertificate($certificate)
    {
        $certificate->alt_names = json_decode($certificate->alt_names);
        $alts = collect($certificate->alt_names);
        if (!$alts->contains($certificate->cname)){
            $alts->push($certificate->cname);
        }

        $certificate->created_at_utc = $certificate->created_at->getTimestamp();
        $certificate->updated_at_utc = $certificate->updated_at->getTimestamp();
        $certificate->valid_from_utc = $certificate->valid_from->getTimestamp();
        $certificate->valid_to_utc = $certificate->valid_to->getTimestamp();

        $certificate->is_expired = $certificate->valid_to->lt(Carbon::now());
        $certificate->is_le = strpos($certificate->issuer, 'Let\'s Encrypt') !== false;
        $certificate->is_cloudflare = $alts->filter(function($val, $key){
            return strpos($val, '.cloudflaressl.com') !== false;
        })->isNotEmpty();

        return $certificate;
    }

    /**
     * Returns a query builder to load the raw certificates (PEM excluded)
     * @param Collection $ids
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function loadCertificates($ids){
        return Certificate::query()
            ->select(
                'id', 'crt_sh_id', 'crt_sh_ca_id', 'fprint_sha1', 'fprint_sha256',
                'valid_from', 'valid_to', 'created_at', 'updated_at', 'cname', 'subject',
                'issuer', 'is_ca', 'is_self_signed', 'parent_id', 'is_le', 'is_cloudflare',
                'is_precert', 'is_precert_ca',
                'alt_names', 'source')
            ->whereIn('id', $ids);
    }

    /**
     * Returns a query builder for getting newest Whois results for the given watch array.
     * TODO: there is no watch association, fix it...
     * @param Collection $watches
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getNewestWhoisScans($watches){
        $table = (new WhoisResult())->getTable();
        $domainsTable = (new BaseDomain())->getTable();

        $qq = WhoisResult::query()
            ->select('x.watch_id')
            ->selectRaw('MAX(x.last_scan_at) AS last_scan')
            ->from($table . ' AS x')
            ->whereIn('x.watch_id', $watches)
            ->groupBy('x.watch_id');
        $qqSql = $qq->toSql();

        $q = WhoisResult::query()
            ->from($table . ' AS s')
            ->select(['s.*', $domainsTable.'.domain_name AS domain'])
            ->join(
                DB::raw('(' . $qqSql. ') AS ss'),
                function(JoinClause $join) use ($qq) {
                    $join->on('s.watch_id', '=', 'ss.watch_id')
                        ->on('s.last_scan_at', '=', 'ss.last_scan')
                        ->addBinding($qq->getBindings());
                })
            ->join($domainsTable, $domainsTable.'.id', '=', 's.domain_id');

        return $q;
    }

    /**
     * Returns a query builder for getting newest CRT SH results for the given watch array.
     *
     * @param Collection $watches
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getNewestCrtshScans($watches){
        $table = (new CrtShQuery())->getTable();

        $qq = CrtShQuery::query()
            ->select('x.watch_id')
            ->selectRaw('MAX(x.last_scan_at) AS last_scan')
            ->from($table . ' AS x')
            ->whereIn('x.watch_id', $watches)
            ->groupBy('x.watch_id');
        $qqSql = $qq->toSql();

        $q = CrtShQuery::query()
            ->from($table . ' AS s')
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
     * Returns the newest TLS scans given the watches of interest and loaded DNS scans
     *
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
     * Builds collection mapping watch_id -> primary IP address.
     *
     * @param Collection $dnsScans
     * @return Collection
     */
    protected function getPrimaryIPs($dnsScans){
        return $dnsScans->values()->mapWithKeys(function ($item) {
            try{
                $primary = empty($item->dns) ? null : $item->dns[0][1];
                return [intval($item->watch_id) => $primary];
            } catch (Exception $e){
                return [];
            }
        });
    }

    /**
     * Processes loaded crtsh scan results
     * @param Collection $crtshScans
     * @return Collection
     */
    protected function processCrtshScans($crtshScans){
        return $crtshScans->mapWithKeys(function ($item){
            try{
                $item->certs_ids = json_decode($item->certs_ids);
            } catch (Exception $e){
            }

            return [intval($item->watch_id) => $item];
        });
    }

    /**
     * Processes loaded tls scan results
     * @param Collection $tlsScans
     * @return Collection
     */
    protected function processTlsScans($tlsScans){
        return $tlsScans->transform(function($val, $key){
            try{
                $val->certs_ids = json_decode($val->certs_ids);
            } catch (Exception $e){
            }

            return $val;
        });
    }

    /**
     * Processes loaded scan results (deserialization)
     * @param Collection $dnsScans
     * @return Collection
     */
    protected function processDnsScans($dnsScans){
        return $dnsScans->mapWithKeys(function ($item) {
            try{
                $item->dns = json_decode($item->dns);
            } catch (Exception $e){
            }

            return [intval($item->watch_id) => $item];
        });
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
     *
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