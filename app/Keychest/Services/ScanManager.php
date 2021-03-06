<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;


use App\Keychest\Utils\DomainTools;
use App\Models\BaseDomain;
use App\Models\Certificate;
use App\Models\CrtShQuery;
use App\Models\DnsEntry;
use App\Models\DnsResult;
use App\Models\HandshakeScan;
use App\Models\LastScanCache;


use App\Models\WatchAssoc;
use App\Models\WatchTarget;
use App\Models\WhoisResult;

use Exception;


use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\DB;

class ScanManager {

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Create a new Auth manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Returns a query builder to load the raw certificates (PEM excluded)
     * @param Collection $ids
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function loadCertificates($ids){
        return Certificate::query()
            ->select(
                'id', 'crt_sh_id', 'crt_sh_ca_id', 'fprint_sha1', 'fprint_sha256',
                'valid_from', 'valid_to', 'created_at', 'updated_at', 'cname', 'subject',
                'issuer', 'is_ca', 'is_self_signed', 'parent_id', 'is_le', 'is_cloudflare',
                'is_precert', 'is_precert_ca',
                'alt_names', 'source',
                'is_ov', 'is_ev', 'is_cn_wildcard', 'is_alt_wildcard', 'issuer_o', 'alt_names_cnt')
            ->whereIn('id', $ids);
    }

    /**
     * Returns a query builder for getting newest Whois results for the given watch array.
     * @param Collection $domainIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getNewestWhoisScans($domainIds){
        $table = WhoisResult::TABLE;
        $domainsTable = BaseDomain::TABLE;

        $qq = WhoisResult::query()
            ->select('x.domain_id')
            ->selectRaw('MAX(x.last_scan_at) AS last_scan')
            ->from($table . ' AS x')
            ->whereIn('x.domain_id', $domainIds->values())
            ->groupBy('x.domain_id');
        $qqSql = $qq->toSql();

        $q = WhoisResult::query()
            ->from($table . ' AS s')
            ->select(['s.*', $domainsTable.'.domain_name AS domain'])
            ->join(
                DB::raw('(' . $qqSql. ') AS ss'),
                function(JoinClause $join) use ($qq) {
                    $join->on('s.domain_id', '=', 'ss.domain_id')
                        ->on('s.last_scan_at', '=', 'ss.last_scan')
                        ->addBinding($qq->getBindings());
                })
            ->join($domainsTable, $domainsTable.'.id', '=', 's.domain_id');

        return $q;
    }

    /**
     * Returns a query builder for getting newest Whois results for the given watch array.
     * Optimized version with last scan table.
     *
     * @param Collection $domainIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getNewestWhoisScansOptim($domainIds){
        $table = WhoisResult::TABLE;
        $domainsTable = BaseDomain::TABLE;
        $lstScanTbl = LastScanCache::TABLE;

        $q = LastScanCache::query()
            ->select(['s.*', $domainsTable.'.domain_name AS domain'])
            ->from($lstScanTbl . ' AS ls')
            ->join($table . ' AS s', function(JoinClause $join){
                $join->on('s.domain_id','=', 'ls.obj_id')
                    ->on('s.id', '=', 'ls.scan_id')
                    ->whereRaw('ls.cache_type = 0')
                    ->whereRaw('ls.scan_type = 4');  // whois
            })
            ->join($domainsTable, $domainsTable.'.id', '=', 's.domain_id')
            ->whereIn('s.domain_id', $domainIds->values());

        return $q;
    }

    /**
     * Returns a query builder for getting newest CRT SH results for the given watch array.
     *
     * @param Collection $watches
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getNewestCrtshScans($watches){
        $table = CrtShQuery::TABLE;

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
     * Returns a query builder for getting newest CRT SH results for the given watch array.
     * Optimized version with last scan result.
     *
     * @param Collection $watches
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getNewestCrtshScansOptim($watches){
        $table = CrtShQuery::TABLE;
        $lstScanTbl = LastScanCache::TABLE;

        $q = LastScanCache::query()
            ->select('s.*')
            ->from($lstScanTbl . ' AS ls')
            ->join($table . ' AS s', function(JoinClause $join){
                $join->on('s.watch_id','=', 'ls.obj_id')
                    ->on('s.id', '=', 'ls.scan_id')
                    ->whereRaw('ls.cache_type = 0')
                    ->whereRaw('ls.scan_type = 3');  // crtsh
            })
            ->whereIn('s.watch_id', $watches);

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
    public function getNewestTlsScans($watches, $dnsScans, $primaryIPs){
        $table = HandshakeScan::TABLE;

        $qq = HandshakeScan::query()
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

        $q = HandshakeScan::query()
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
     * Returns the newest TLS scans given the watches of interest and loaded DNS scans
     * Optimized version with last scan cache
     *
     * @param $watches
     * @param boolean $primaryIPs
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getNewestTlsScansOptim($watches, $primaryIPs=false){
        $table = HandshakeScan::TABLE;
        $watchTbl = WatchTarget::TABLE;
        $dnsTbl = DnsResult::TABLE;
        $dnsEntryTbl = DnsEntry::TABLE;
        $lstScanTbl = LastScanCache::TABLE;

        $q = WatchTarget::query()
            ->select('xh.*')
            ->from($watchTbl . ' AS w')
            ->join($dnsTbl.' AS xd', 'xd.id', '=', 'w.last_dns_scan_id')
            ->join($dnsEntryTbl . ' AS xde', function(JoinClause $join) use ($primaryIPs) {
                $join->on('xde.scan_id', '=', 'xd.id');
                if ($primaryIPs){
                    $join->whereRaw('xde.res_order = 0');
                }
            })
            ->join($lstScanTbl . ' AS ls', function(JoinClause $join){
                $join->whereRaw('ls.cache_type = 0')
                    ->whereRaw('ls.scan_type = 2')  // TLS
                    ->on('ls.obj_id', '=', 'w.id')
                    ->on('ls.aux_key', '=', 'xde.ip');
            })
            ->join($table . ' AS xh', function(JoinClause $join) {
                $join->on('xh.id', '=', 'ls.scan_id');
            })
            ->whereIn('w.id', $watches);

        return $q;
    }

    /**
     * Returns a query builder for getting newest DNS results for the given watch array.
     *
     * @param Collection $watches
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getNewestDnsScans($watches){
        // DNS records for given watches.
        // select * from scan_dns s
        // inner join (
        //      select x.watch_id, max(x.last_scan_at) as last_scan
        //      from scan_dns x
        //      WHERE x.watch_id IN (23)
        //      group by x.watch_id ) ss
        // ON s.watch_id = ss.watch_id AND s.last_scan_at = ss.last_scan;
        $dnsTable = DnsResult::TABLE;

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
     * Returns a query builder for getting newest DNS results for the given watch array.
     * Optimized version using last dns id info from the watch.
     *
     * @param Collection $watches
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getNewestDnsScansOptim($watches){
        $dnsTable = DnsResult::TABLE;
        $watchTbl = WatchTarget::TABLE;

        $q = WatchTarget::query()
            ->select('s.*')
            ->from($watchTbl . ' AS w')
            ->join($dnsTable . ' AS s', function(JoinClause $join){
                $join->on('s.id','=', 'w.last_dns_scan_id');
            })
            ->whereIn('w.id', $watches);

        return $q;
    }

    /**
     * Returns the query builder for the active watchers for the user id.
     *
     * @param $owner_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getActiveWatcher($owner_id){
        $watchTbl = WatchTarget::TABLE;
        $watchAssocTbl = WatchAssoc::TABLE;
        $baseDomainTbl = BaseDomain::TABLE;

        $query = WatchAssoc::query()
            ->join($watchTbl, $watchTbl.'.id', '=', $watchAssocTbl.'.watch_id')
            ->select($watchTbl.'.*', $watchAssocTbl.'.*', $watchTbl.'.id as wid', $baseDomainTbl.'.domain_name AS domain')
            ->leftJoin($baseDomainTbl, $baseDomainTbl.'.id', '=', $watchTbl.'.top_domain_id')
            ->where($watchAssocTbl.'.owner_id', '=', $owner_id)
            ->whereNull($watchAssocTbl.'.deleted_at')
            ->whereNull($watchAssocTbl.'.disabled_at');
        return $query;
    }

    /**
     * Processes loaded scan results (deserialization)
     * @param Collection $dnsScans
     * @return Collection
     */
    public function processDnsScans($dnsScans){
        return $dnsScans->mapWithKeys(function ($item) {
            try{
                $this->processDnsScan($item);
            } catch (Exception $e){
            }

            return [intval($item->watch_id) => $item];
        });
    }

    /**
     * Single DNS scan processing.
     * @param $scan
     * @return mixed
     */
    public function processDnsScan($scan){
        if (empty($scan)){
            return $scan;
        }

        $scan->dns = json_decode($scan->dns);
        return $scan;
    }

    /**
     * Processes loaded tls scan results
     * @param Collection $tlsScans
     * @return Collection
     */
    public function processTlsScans($tlsScans){
        return $tlsScans->transform(function($val, $key){
            try{
                $this->processTlsScan($val);
            } catch (Exception $e){
            }

            return $val;
        });
    }

    /**
     * Processing single TLS scan
     * @param $scan
     * @return mixed
     */
    public function processTlsScan($scan){
        if (empty($scan)){
            return $scan;
        }

        $scan->certs_ids = json_decode($scan->certs_ids);
        return $scan;
    }

    /**
     * Processes loaded crtsh scan results
     * @param Collection $crtshScans
     * @return Collection
     */
    public function processCrtshScans($crtshScans){
        return $crtshScans->mapWithKeys(function ($item){
            try{
                $this->processCrtshScan($item);
            } catch (Exception $e){
            }

            return [intval($item->watch_id) => $item];
        });
    }

    /**
     * Processing single CRTSH scan.
     * @param $scan
     * @return mixed
     */
    public function processCrtshScan($scan){
        if (empty($scan)){
            return $scan;
        }

        $scan->certs_ids = json_decode($scan->certs_ids);
        return $scan;
    }

    /**
     * Processes loaded Whois scan results
     * @param Collection $whoisScans
     * @return Collection
     */
    public function processWhoisScans($whoisScans){
        return $whoisScans->mapWithKeys(function ($item){
            $this->processWhoisScan($item);
            return [intval($item->domain_id) => $item];
        });
    }

    /**
     * Processing single Whois scan.
     * @param $scan
     * @return mixed
     */
    public function processWhoisScan($scan){
        if (empty($scan)){
            return $scan;
        }

        try{
            $scan->dns = json_decode($scan->dns);
        } catch (Exception $e){
        }

        try{
            $scan->emails = json_decode($scan->emails);
        } catch (Exception $e){
        }

        return $scan;
    }

    /**
     * Extends watches with url names.
     * @param $watch
     * @return mixed
     */
    public function extendWatchWithUrls($watch){
        $strPort = empty($watch->scan_port) ? 443 : intval($watch->scan_port);
        $watch->url = DomainTools::buildUrl($watch->scan_scheme, $watch->scan_host, $strPort);
        $watch->url_short = DomainTools::buildUrl($watch->scan_scheme, $watch->scan_host, $strPort === 443 ? null : $strPort);
        $watch->host_port = $watch->scan_host . ($strPort == 0 || $strPort === 443 ? '' : ':' . $strPort);
        return $watch;
    }
}
