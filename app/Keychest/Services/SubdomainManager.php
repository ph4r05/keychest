<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;

use App\Keychest\Utils\DomainTools;
use App\Models\SubdomainScanBlacklist;
use App\Models\SubdomainWatchAssoc;
use App\Models\SubdomainWatchTarget;
use App\Models\User;


use Illuminate\Support\Collection;

use Illuminate\Support\Facades\DB;


class SubdomainManager {

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
     * Returns true if the given domain is somehow blacklisted
     * @param $hostname
     * @return bool
     */
    public function isBlacklisted($hostname){
        // select * from subdomain_scan_blacklist where 'wp12.com' LIKE CONCAT('%', rule) ;
        $q = SubdomainScanBlacklist::query()
            ->where(function($q) use ($hostname){
                $q->where('rule_type', '=', 0)
                    ->whereRaw(DB::raw('? LIKE CONCAT("%", rule)'), [$hostname]);
            })->orWhere(function($q) use ($hostname){
                $q->where('rule_type', '=', 1)
                    ->where('rule', '=', $hostname);
            });
        return $q->get()->isNotEmpty();
    }

    /**
     * Returns number of hosts used by the user
     * @param null $ownerId
     * @return int
     */
    public function numDomainsUsed($ownerId){
        return SubdomainWatchAssoc::query()
            ->where('owner_id', $ownerId)
            ->whereNull('deleted_at')
            ->whereNull('disabled_at')->count();
    }

    /**
     * Checks if the host can be added to the certificate monitor
     * @param $server
     * @param User|null $curUser
     * @return int
     */
    public function canAdd($server, $curUser=null){
        $parsed = parse_url($server);
        if (empty($parsed) || !DomainTools::isValidParsedUrlHostname($parsed)){
            return -1;
        }

        $criteria = $this->buildCriteria($parsed, $server);
        $ownerId = empty($curUser) ? null : $curUser->primary_owner_id;

        $allMatchingHosts = $this->getAllHostsBy($criteria, $ownerId);
        return !$this->allHostsEnabled($allMatchingHosts);
    }

    /**
     * Returns all host associations for the given user
     * @param $ownerId
     * @param Collection $hosts collection of host ids to restrict
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getHostAssociations($ownerId, $hosts=null){
        $query = SubdomainWatchAssoc::query()->where('owner_id', $ownerId);
        if (!empty($hosts) && $hosts->isNotEmpty()){
            $query->whereIn('watch_id', $hosts);
        }

        return $query->get();
    }

    /**
     * Used to load hosts for update / add to detect duplicates
     * @param $criteria
     * @param $ownerId user ID criteria for the match
     * @param $assoc Collection association loaded for the given user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getHostsByQuery($criteria=null, $ownerId=null, $assoc=null){
        $query = SubdomainWatchTarget::query();

        if (!empty($criteria)) {
            foreach ($criteria as $key => $val) {
                $query = $query->where($key, $val);
            }
        }

        if (!empty($ownerId)){
            $assoc = $this->getHostAssociations($ownerId);
        }

        if (!empty($assoc)){
            $query->whereIn('id', $assoc->pluck('watch_id'));
        }

        return $query;
    }

    public function getHostsWithInvSuffix($host, $ownerId, $withDot, $enabledOnly){
        $assocTbl = SubdomainWatchAssoc::TABLE;
        $domainTbl = SubdomainWatchTarget::TABLE;

        $q = SubdomainWatchAssoc::query()
            ->select('d.*',
                'a.deleted_at AS assoc_deleted_at',
                'a.disabled_at AS assoc_disabled_at',
                'a.created_at AS assoc_created_at',
                'a.auto_fill_watches AS assoc_auto_fill_watches'
            )
            ->from($assocTbl. ' AS a')
            ->join($domainTbl. ' AS d', 'd.id', '=', 'a.watch_id')
            ->where('a.owner_id', $ownerId);

        if ($enabledOnly){
            $q = $q->whereNull('a.deleted_at');
        }

        if ($withDot){
            $q = $q->whereRaw(DB::raw('? LIKE CONCAT("%.", d.scan_host)'), [$host]);
        } else {
            $q = $q->whereRaw(DB::raw('? LIKE CONCAT("%", d.scan_host)'), [$host]);
        }

        return $q;
    }

    /**
     * Query builder for host suffix loading.
     * @param $suffix
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getHostsBySuffixQuery($suffix){
        return SubdomainWatchTarget::query()->where('scan_host', 'like', '%' . $suffix);
    }

    /**
     * Query builder for host suffix loading.
     * Returns query for hosts that are suffices of the given input.
     * e.g., google.com host will match input test.google.com
     * @param $suffix
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getHostsByInvSuffixQuery($suffix){
        return SubdomainWatchTarget::query()->whereRaw(DB::raw('? LIKE CONCAT("%", scan_host)'), [$suffix]);
    }

    /**
     * Query builder for host suffix loading.
     * Returns query for hosts that are suffices of the given input. Dot is required.
     * e.g., google.com host will match input test.google.com
     * @param $host
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getHostsByInvSuffixDotQuery($host){
        return SubdomainWatchTarget::query()->whereRaw(DB::raw('? LIKE CONCAT("%.", scan_host)'), [$host]);
    }

    /**
     * Used to load hosts for update / add to detect duplicates
     * @param $criteria
     * @param $ownerId user ID criteria for the match
     * @param $assoc Collection association loaded for the given user
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getHostsBy($criteria=null, $ownerId=null, $assoc=null){
        return $this->getHostsByQuery($criteria, $ownerId, $assoc)->get();
    }

    /**
     * Loads all hosts associated to the user, augments host records with the association record.
     * @param $criteria
     * @param $ownerId
     * @param $assoc Collection
     * @return Collection
     */
    function getAllHostsBy($criteria, $ownerId=null, $assoc=null){
        if (!empty($ownerId)) {
            $assoc = $this->getHostAssociations($ownerId);
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

        return $this->numHostsEnabled($hosts) == $hosts->count();
    }

    /**
     * Returns true if all hosts in the collection are enabled in the association.
     * If association is empty, true is returned.
     * Helper function for CRUD hosts.
     * @param $hosts Collection
     * @return int
     */
    public function numHostsEnabled($hosts){
        if ($hosts->isEmpty()){
            return 0;
        }

        return $hosts->map(function ($item, $key){
            return !empty($item->getAssoc()) ? empty($item->getAssoc()->deleted_at) : 1;
        })->sum();
    }

    /**
     * Filters out disabled hosts
     * @param Collection $hosts
     * @return Collection
     */
    public function filterDisabled($hosts){
        return $hosts->reject(function ($item, $key){
            return !empty($item->getAssoc()) ? !empty($item->getAssoc()->deleted_at) : true;
        })->values();
    }

    /**
     * Builds simple criteria from parsed domain
     * @param $parsed
     * @param $server
     * @return array
     */
    public function buildCriteria($parsed, $server=null){
        return [
            'scan_host' => isset($parsed['host']) && !empty($parsed['host']) ? $parsed['host'] : DomainTools::removeUrlPath($server),
        ];
    }

}
