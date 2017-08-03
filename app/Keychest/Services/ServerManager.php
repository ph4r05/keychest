<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;

use App\Keychest\Utils\DomainTools;
use App\Models\DnsEntry;
use App\Models\DnsResult;
use App\Models\HandshakeScan;
use App\Models\LastScanCache;
use App\Models\WatchAssoc;
use App\Models\WatchTarget;
use App\User;
use function foo\func;
use Illuminate\Contracts\Auth\Factory as FactoryContract;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServerManager {

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
     * Returns number of hosts used by the user
     * @param null $userId
     * @return int
     */
    public function numHostsUsed($userId){
        return WatchAssoc::query()
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->whereNull('disabled_at')->count();
    }

    /**
     * Checks if the host can be added to the certificate monitor
     * @param $server
     * @param User|null $curUser
     * @return int
     */
    public function canAddHost($server, $curUser=null){
        $parsed = parse_url($server);
        if (empty($parsed) || !DomainTools::isValidParsedUrlHostname($parsed)){
            return -1;
        }

        $criteria = $this->buildCriteria($parsed, $server);
        $userId = empty($curUser) ? null : $curUser->getAuthIdentifier();

        $allMatchingHosts = $this->getAllHostsBy($criteria, $userId);
        return !$this->allHostsEnabled($allMatchingHosts);
    }

    /**
     * Returns query for loading users hosts.
     * @param $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getUserHostsQuery($userId){
        $watchTbl = (new WatchTarget())->getTable();
        $watchAssocTbl = (new WatchAssoc())->getTable();

        $query = WatchAssoc::query()
            ->join($watchTbl, $watchTbl.'.id', '=', $watchAssocTbl.'.watch_id')
            ->select($watchTbl.'.*', $watchAssocTbl.'.*')
            ->where($watchAssocTbl.'.user_id', '=', $userId)
            ->whereNull($watchAssocTbl.'.deleted_at');
        return $query;
    }

    /**
     * Returns all host associations for the given user
     * @param $userId
     * @param Collection $hosts collection of host ids to restrict
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getHostAssociations($userId, $hosts=null){
        $query = WatchAssoc::query()->where('user_id', $userId);
        if (!empty($hosts) && $hosts->isNotEmpty()){
            $query->whereIn('watch_id', $hosts);
        }

        return $query->get();
    }

    /**
     * Used to load hosts for update / add to detect duplicates
     * @param $criteria
     * @param $userId user ID criteria for the match
     * @param $assoc Collection association loaded for the given user
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getHostsBy($criteria=null, $userId=null, $assoc=null){
        $query = WatchTarget::query();

        if (!empty($criteria)) {
            foreach ($criteria as $key => $val) {
                $query = $query->where($key, $val);
            }
        }

        if (!empty($userId)){
            $assoc = $this->getHostAssociations($userId);
        }

        if (!empty($assoc)){
            $query->whereIn('id', $assoc->pluck('watch_id'));
        }

        return $query->get();
    }

    /**
     * Loads all hosts associated to the user, augments host records with the association record.
     * @param $criteria
     * @param $userId
     * @param $assoc Collection
     * @return Collection
     */
    function getAllHostsBy($criteria, $userId=null, $assoc=null){
        if (!empty($userId)) {
            $assoc = $this->getHostAssociations($userId);
        }
        $hosts = $this->getHostsBy($criteria, null, $assoc);
        return $this->augmentHostsWithAssoc($hosts, $assoc);
    }

    /**
     * Filters out hosts that do not match the association
     * @param Collection $hosts
     * @param Collection $assoc
     * @return Collection
     */
    public function filterHostsWithAssoc($hosts, $assoc){
        return $this->augmentHostsWithAssoc($hosts, $assoc)->reject(function($value, $item){
            return empty($value->getAssoc());
        });
    }

    /**
     * Merges host record with association (loaded for single user)
     * @param Collection $hosts
     * @param Collection $assoc
     * @return Collection
     */
    public function augmentHostsWithAssoc($hosts, $assoc){
        if (empty($assoc)){
            return $hosts;
        }

        $assocMap = $assoc->mapWithKeys(function($item){
            return [$item['watch_id'] => $item];
        });

        return $hosts->map(function($item, $key) use ($assocMap){
            $item->setAssoc($assocMap->get($item->id));
            return $item;
        });
    }

    /**
     * Returns true if all hosts in the collection are enabled in the association.
     * If association is empty, true is returned.
     * Helper function for CRUD hosts.
     * @param $hosts Collection
     * @return bool
     */
    public function allHostsEnabled($hosts){
        if ($hosts->isEmpty()){
            return false;
        }

        return $hosts->map(function ($item, $key){
            return !empty($item->getAssoc()) ? empty($item->getAssoc()->deleted_at) : 1;
        })->sum() == $hosts->count();
    }

    /**
     * Builds simple criteria from parsed domain
     * @param $parsed
     * @param $server
     * @return array
     */
    public function buildCriteria($parsed, $server=null){
        return [
            'scan_scheme' => isset($parsed['scheme']) && !empty($parsed['scheme']) ? $parsed['scheme'] : 'https',
            'scan_host' => isset($parsed['host']) && !empty($parsed['host']) ? $parsed['host'] : DomainTools::removeUrlPath($server),
            'scan_port' => isset($parsed['port']) && !empty($parsed['port']) ? $parsed['port'] : 443,
        ];
    }

    /**
     * Replaces http/80 with https/443 as it makes no sense to scan 80 for tls.
     * @param $criteria
     * @return mixed
     */
    public function replaceHttp($criteria){
        $scheme = $criteria['scan_scheme'];
        $port = $criteria['scan_port'];
        if ($scheme == 'http' || $port === 80){
            $criteria['scan_scheme'] = 'https';
            $criteria['scan_port'] = 443;
        }
        return $criteria;
    }

    /**
     * Builds query to load server list, includes also aux columns
     * dns_error, tls_errors to indicate an error in the server listing.
     *
     * The columns are in the main query so we can filter & sort on it.
     * Possible optimizations:
     *  - extract last tls scan info to a separate joinable table so no aggregation on MAX(last_scan_at) is needed
     *  - if tls_errors is not in the sort field, we can load all IDs by the side, cache it, match on it on the query.
     */
    public function loadServerList(){
//        The SQL built here is like this:
//        ----------------------------------------------------------------------------
//        SELECT `watch_target`.*,
//             `user_watch_target`.*,
//             `scan_dns`.`status`  AS `dns_status`,
//             `scan_dns`.`num_res` AS `dns_num_res`,
//             ( CASE
//                 WHEN scan_dns.status IS NULL
//                  OR scan_dns.status != 1
//                  OR scan_dns.num_res = 0 THEN 1
//                 ELSE 0
//               end )              AS dns_error,
//             ( CASE
//                 WHEN sl.tls_errors IS NULL THEN 0
//                 ELSE sl.tls_errors
//               end )              AS tls_errors
//        FROM   `user_watch_target`
//             INNER JOIN `watch_target`
//                     ON `watch_target`.`id` = `user_watch_target`.`watch_id`
//             LEFT JOIN `scan_dns`
//                    ON `scan_dns`.`id` = `watch_target`.`last_dns_scan_id`
//             LEFT JOIN (SELECT Count(*) AS tls_errors,
//                               w.id
//                        FROM   watch_target w
//                               LEFT JOIN `scan_dns` xd
//                                      ON xd.`id` = w.`last_dns_scan_id`
//                               LEFT JOIN `scan_dns_entry` xde
//                                      ON xde.scan_id = xd.`id`
//                              LEFT JOIN `last_scan_cache` AS `ls`
//                                      ON ls.cache_type = 0
//                                      AND ls.scan_type = 2
//                                      AND `ls`.`obj_id` = `w`.`id`
//                                      AND `ls`.`aux_key` = `xde`.`ip`
//                               LEFT JOIN `scan_handshakes` xh
//                                      ON xh.id = ls.scan_id
//                        WHERE  1
//        AND xh.err_code IS NOT NULL
//        AND xh.err_code != 0
//        AND xh.err_code != 1
//                        GROUP  BY w.id) sl
//                    ON sl.id = watch_target.id
//        ----------------------------------------------------------------------------

        $watchTbl = (new WatchTarget())->getTable();
        $watchAssocTbl = (new WatchAssoc())->getTable();
        $dnsTable = (new DnsResult())->getTable();
        $dnsEntryTable = (new DnsEntry())->getTable();
        $tlsScanTbl = (new HandshakeScan())->getTable();
        $lastScanTbl = (new LastScanCache())->getTable();

        $qsl = WatchTarget::query()
            ->select('w.id')
            ->selectRaw('
                SUM(CASE WHEN 
                    xh.err_code is NOT NULL AND xh.err_code <> 0 AND xh.err_code <> 1 THEN 1 ELSE 0 END
                ) AS tls_errors')
            ->selectRaw('COUNT(*) AS tls_all')
            ->from($watchTbl . ' AS w')
            ->leftJoin($dnsTable.' AS xd', 'xd.id', '=', 'w.last_dns_scan_id')
            ->leftJoin($dnsEntryTable . ' AS xde', 'xde.scan_id', '=', 'xd.id')
            ->leftJoin($lastScanTbl . ' AS ls', function(JoinClause $join){
                $join->whereRaw('ls.cache_type = 0')
                    ->whereRaw('ls.scan_type = 2')
                    ->on('ls.obj_id', '=', 'w.id')
                    ->on('ls.aux_key', '=', 'xde.ip');
            })

            ->leftJoin($tlsScanTbl . ' AS xh', function(JoinClause $join) {
                $join->on('xh.id', '=', 'ls.scan_id');
            })
            ->groupBy('w.id');

        $query = WatchAssoc::query()
            ->join($watchTbl, $watchTbl.'.id', '=', $watchAssocTbl.'.watch_id')
            ->leftJoin($dnsTable, $dnsTable.'.id', '=', $watchTbl.'.last_dns_scan_id')
            ->select(
                $watchTbl.'.*',
                $watchAssocTbl.'.*',
                $dnsTable.'.status AS dns_status',
                $dnsTable.'.num_res AS dns_num_res',
                DB::raw('(CASE WHEN '.$dnsTable.'.status IS NULL'.
                    ' OR '.$dnsTable.'.status!=1' .
                    ' OR '.$dnsTable.'.num_res=0 THEN 1 ELSE 0 END) AS dns_error'),
                DB::raw('(CASE WHEN sl.tls_errors IS NULL THEN 0 ELSE sl.tls_errors END) AS tls_errors'),
                DB::raw('(CASE WHEN sl.tls_all IS NULL THEN 0 ELSE sl.tls_all END) AS tls_all')
            )
            ->leftJoin(
                DB::raw('(' . $qsl->toSql() . ') AS sl'),
                function(JoinClause $join) use ($qsl){
                    $join->on('sl.id', '=', 'watch_target.id');
                    $join->addBinding($qsl->getBindings());
                });
        return $query;
    }

}
