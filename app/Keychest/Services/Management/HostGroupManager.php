<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 16.08.17
 * Time: 18:16
 */

namespace App\Keychest\Services\Management;

use App\Keychest\DataClasses\GeneratedSshKey;
use App\Keychest\DataClasses\HostRecord;
use App\Keychest\DataClasses\ValidityDataModel;
use App\Keychest\Services\Exceptions\CertificateAlreadyInsertedException;
use App\Keychest\Services\Exceptions\SshKeyAllocationException;
use App\Keychest\Utils\ApiKeyLogger;
use App\Keychest\Utils\DataTools;
use App\Keychest\Utils\DomainTools;
use App\Models\ApiKey;
use App\Models\ApiWaitingObject;
use App\Models\ManagedHost;
use App\Models\ManagedHostGroup;
use App\Models\SshKey;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use phpseclib\Crypt\RSA;
use Webpatser\Uuid\Uuid;


class HostGroupManager
{

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Create a new Auth manager instance.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Builds query to load group list.
     *
     * @param null $ownerId
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function loadGroupListQuery($ownerId=null){
        $query = ManagedHost::query();
        if ($ownerId){
            $query = $query->where('owner_id', '=', $ownerId);
        }

        return $query;
    }

    /**
     * Query for group search / autocomplete
     * @param $ownerId
     * @param $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function searchHostGroupQuery($ownerId, $search){
        return ManagedHostGroup::query()
            ->where('owner_id', '=', $ownerId)
            ->where('group_name', 'like', '%' . $search . '%');
    }

    /**
     * Adds a new single host host group.
     * @param $ownerId
     * @param null $groupName
     * @return ManagedHostGroup
     */
    public function addSingleHostGroup($ownerId, $groupName=null){
        $hostGroup = new ManagedHostGroup([
            'group_name' => $groupName ?: 'group-'. Str::random(16),
            'owner_id' => $ownerId
        ]);

        $hostGroup->save();

        if (empty($groupName)) {
            $hostGroup->group_name = 'group-' . $hostGroup->id;
            $hostGroup->save();
        }

        return $hostGroup;
    }

    /**
     * Sanitizes group by reloading them from the database
     * making sure the owner has access to those groups by the ID.
     *
     * @param $groups
     * @param $ownerId
     * @return mixed
     */
    public function sanitizeGroupsByReload($groups, $ownerId){
        list($loadable, $rest) = $groups->partition(function($item){
            return isset($item->id);
        });

        $groupIds = $loadable->map->id->values();
        $loaded = ManagedHostGroup::query()
            ->where('owner_id', '=', $ownerId)
            ->whereIn('id', $groupIds)
            ->get();

        return $rest->merge($loaded);
    }

    /**
     * Fetches existing groups or creates a new ones
     * @param $groups
     * @param $ownerId
     * @return mixed
     * @throws \Exception
     */
    public function fetchOrCreate($groups, $ownerId){
        list($existing, $fetchList) = $groups->partition(function($item){
            return isset($item->id);
        });

        $keysToFetch = $fetchList->keyBy('group_name');
        if ($keysToFetch->isEmpty()){
            return $groups;
        }

        Log::info($keysToFetch->toJson());
        $fetched = collect();
        $batchSize = 1;

        // As race conditions may occur this needs to be performed in a loop.
        // Unique constrain may be triggered if another thread inserts the same (owner_id, group_name).
        $attempts = 0;
        for(; $attempts < (10 + $keysToFetch->count()) && $keysToFetch->isNotEmpty(); $attempts+=1){

            // Fetch groups in chunks (batch size)
            $chunks = $keysToFetch->keys()->chunk($batchSize);
            $fetchedNow = collect();
            foreach ($chunks as $chunk) {
                $fetchedNow = $fetchedNow->merge(
                    ManagedHostGroup::query()
                        ->where('owner_id', '=', $ownerId)
                        ->whereIn('group_name', $chunk)
                        ->get()
                        ->keyBy('group_name'));
            }

            // Remove fetched keys from the $keysToFetch.
            $fetched = $fetched->merge($fetchedNow);
            $keysToFetch = $keysToFetch->reject(function($value, $key) use ($fetchedNow) {
                return $fetchedNow->has($key);
            });

            // Try to insert a new group, one by one
            foreach (collect($keysToFetch) as $group_name => $group_data) {
                try {
                    $grp = new ManagedHostGroup();
                    $grp->group_name = $group_name;
                    $grp->owner_id = $ownerId;
                    $grp->save();

                    $fetched->put($group_name, $grp);
                    $keysToFetch->forget($group_name);

                } catch (QueryException $e) {
                    continue;
                }
            }
        }

        if ($keysToFetch->isNotEmpty()){
            throw new \Exception('Could not fetch/insert');
        }

        return $existing->merge($fetched->values());
    }
}

