<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;

use App\Keychest\Services\Exceptions\InvalidHostname;
use App\Keychest\Utils\DomainTools;
use App\Models\DnsEntry;
use App\Models\DnsResult;
use App\Models\HandshakeScan;
use App\Models\LastScanCache;
use App\Models\WatchAssoc;
use App\Models\WatchService;
use App\Models\WatchTarget;
use App\Models\User;


use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\DB;

class ServerManager {

    const FILTER_INCLUDE = 1;
    const FILTER_EXCLUDE = 2;
    const FILTER_ONLY = 3;

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

        $allMatchingHosts = $this->getAllHostsWithAssociationsBy($criteria, $userId);
        return !$this->allHostsEnabled($allMatchingHosts);
    }

    /**
     * Returns query for loading users hosts.
     * @param $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getUserHostsQuery($userId){
        $watchTbl = WatchTarget::TABLE;
        $watchAssocTbl = WatchAssoc::TABLE;

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
     * Builds query to load hosts by the defined criteria.
     *
     * @param $criteria
     * @param $userId user ID criteria for the match
     * @param $assoc Collection association loaded for the given user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getHostsQueryBy($criteria=null, $userId=null, $assoc=null){
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

        return $query;
    }

    /**
     * Used to load hosts for update / add to detect duplicates
     * @param $criteria
     * @param $userId user ID criteria for the match
     * @param $assoc Collection association loaded for the given user
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getHostsBy($criteria=null, $userId=null, $assoc=null){
        return $this->getHostsQueryBy($criteria, $userId, $assoc)->get();
    }

    /**
     * Loads all hosts associated to the user, augments host records with the association record.
     * @param $criteria
     * @param $userId
     * @param $assoc Collection
     * @return Collection
     */
    function getAllHostsWithAssociationsBy($criteria, $userId=null, $assoc=null){
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
     * Loads hostnames by the given URL
     * @param $url
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws InvalidHostname
     */
    public function loadHostQueryByUrl($url){
        $domain = DomainTools::normalizeUserDomainInput($url);
        $parsed = parse_url($domain);

        if (empty($parsed) || !DomainTools::isValidParsedUrlHostname($parsed)){
            throw new InvalidHostname('Hostname is not valid');
        }

        $criteria = $this->buildCriteria($parsed, $url);
        return $this->getHostsQueryBy($criteria);
    }

    /**
     * Load server errors for the user
     * @param int|null $userId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function loadServerErrorsQuery($userId=null){
        $watchTbl = WatchTarget::TABLE;
        $watchAssocTbl = WatchAssoc::TABLE;
        $dnsTable = DnsResult::TABLE;
        $dnsEntryTable = DnsEntry::TABLE;
        $tlsScanTbl = HandshakeScan::TABLE;
        $lastScanTbl = LastScanCache::TABLE;

        $qsl = WatchTarget::query()
            ->select('w.id')
            ->selectRaw('
                SUM(CASE WHEN 
                    xh.err_code is NOT NULL AND xh.err_code <> 0 AND xh.err_code <> 1 THEN 1 ELSE 0 END
                ) AS tls_errors')
            ->selectRaw('COUNT(*) AS tls_all')
            ->from($watchTbl . ' AS w')
            ->join($dnsTable.' AS xd', 'xd.id', '=', 'w.last_dns_scan_id')
            ->join($dnsEntryTable . ' AS xde', 'xde.scan_id', '=', 'xd.id')
            ->join($lastScanTbl . ' AS ls', function(JoinClause $join){
                $join->whereRaw('ls.cache_type = 0')
                    ->whereRaw('ls.scan_type = 2')
                    ->on('ls.obj_id', '=', 'w.id')
                    ->on('ls.aux_key', '=', 'xde.ip');
            })

            ->join($tlsScanTbl . ' AS xh', function(JoinClause $join) {
                $join->on('xh.id', '=', 'ls.scan_id');
            });

        if ($userId){
            $qsl = $qsl
                ->join($watchAssocTbl, $watchAssocTbl.'.watch_id', '=', 'w.id')
                ->where($watchAssocTbl.'.user_id', '=', $userId)
                ->whereNull($watchAssocTbl.'.deleted_at');
        }

        $qsl = $qsl->groupBy('w.id');
        return $qsl;
    }

    /**
     * Builds query to load server list, includes also aux columns
     * dns_error, tls_errors to indicate an error in the server listing.
     *
     * 'id' returned in the query is ID of the watch association.
     *
     * The columns are in the main query so we can filter & sort on it.
     * Possible optimizations:
     *  - extract last tls scan info to a separate joinable table so no aggregation on MAX(last_scan_at) is needed
     *  - if tls_errors is not in the sort field, we can load all IDs by the side, cache it, match on it on the query.
     * @param null $userId
     * @param int $filterIpServers can exclude / include the IP servers
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function loadServerList($userId=null, $filterIpServers=self::FILTER_INCLUDE){
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
//               END )              AS dns_error,
//             ( CASE
//                 WHEN sl.tls_errors IS NULL THEN 0
//                 ELSE sl.tls_errors
//               END )              AS tls_errors
//             ( CASE
//                 WHEN sl.tls_all IS NULL THEN 0
//                 ELSE sl.tls_all
//               END )              AS tls_errors
//        FROM   `user_watch_target`
//             INNER JOIN `watch_target`
//                     ON `watch_target`.`id` = `user_watch_target`.`watch_id`
//             LEFT JOIN `scan_dns`
//                    ON `scan_dns`.`id` = `watch_target`.`last_dns_scan_id`
//             LEFT JOIN (SELECT
//                               SUM(CASE WHEN
//                                  xh.err_code is NOT NULL AND xh.err_code <> 0 AND xh.err_code <> 1 THEN 1 ELSE 0 END
//                               ) AS tls_errors
//                               Count(*) AS tls_all,
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
//                        GROUP  BY w.id) sl
//                    ON sl.id = watch_target.id
//        ----------------------------------------------------------------------------

        $watchTbl = WatchTarget::TABLE;
        $watchAssocTbl = WatchAssoc::TABLE;
        $dnsTable = DnsResult::TABLE;
        $serviceTable = WatchService::TABLE;

        // Server error query
        $qsl = $this->loadServerErrorsQuery($userId);

        // Basic loading query
        $query = WatchTarget::query()
            ->join($watchAssocTbl, $watchTbl.'.id', '=', $watchAssocTbl.'.watch_id')
            ->leftJoin($dnsTable, $dnsTable.'.id', '=', $watchTbl.'.last_dns_scan_id')
            ->leftJoin($serviceTable, $serviceTable.'.id', '=', $watchTbl.'.service_id')
            ->select(
                $watchTbl.'.*',
                $watchAssocTbl.'.*',
                $dnsTable.'.status AS dns_status',
                $dnsTable.'.num_res AS dns_num_res',
                $dnsTable.'.num_ipv4 AS dns_num_ipv4',
                $dnsTable.'.num_ipv6 AS dns_num_ipv6',
                $dnsTable.'.dns AS dns_json',
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

        if ($userId){
            $query = $query
                ->where($watchAssocTbl.'.user_id', '=', $userId)
                ->whereNull($watchAssocTbl.'.deleted_at')
                ->whereNull($watchAssocTbl.'.disabled_at');
        }

        if ($filterIpServers == self::FILTER_EXCLUDE){
            $query = $query->whereNull($watchTbl.'.ip_scan_id');
        } elseif ($filterIpServers == self::FILTER_ONLY){
            $query = $query->whereNotNull($watchTbl.'.ip_scan_id');
        }

        $query = $query->with('service');
        return $query;
    }

}
