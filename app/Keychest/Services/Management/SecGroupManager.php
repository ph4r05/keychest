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
use App\Models\ManagedSecGroup;
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


class SecGroupManager
{

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Create a new manager instance.
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
    public function loadListQuery($ownerId=null){
        $query = ManagedSecGroup::query();
        if ($ownerId){
            $query = $query->where('owner_id', '=', $ownerId);
        }

        return $query;
    }

    /**
     * Get sec group by ID
     * @param $id
     * @param $ownerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQuery($id, $ownerId){
        return ManagedSecGroup::query()
            ->where('id', $id)
            ->where('owner_id', $ownerId)
            ->with(['services']);
    }

    /**
     * Query for group search / autocomplete
     * @param $ownerId
     * @param $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function searchGroupQuery($ownerId, $search){
        return ManagedSecGroup::query()
            ->where('owner_id', '=', $ownerId)
            ->where('sgrp_name', 'like', '%' . $search . '%');
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
        $loaded = ManagedSecGroup::query()
            ->where('owner_id', '=', $ownerId)
            ->whereIn('id', $groupIds)
            ->get();

        return $rest->merge($loaded);
    }

}

