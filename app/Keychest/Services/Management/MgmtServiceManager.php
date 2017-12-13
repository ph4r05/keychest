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
use App\Models\ManagedService;
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


class MgmtServiceManager
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
     * Builds query to load host list.
     *
     * @param null $ownerId
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function loadServiceListQuery($ownerId=null){
        $query = ManagedService::query();
        if ($ownerId){
            $query = $query->where('owner_id', '=', $ownerId);
        }

        return $query;
    }

    /**
     * Loads host by defined specs
     * @param $svcName
     * @param $ownerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getByName($svcName, $ownerId){
        $q = ManagedService::query()
            ->where('svc_name', '=', $svcName);
        $q = $q->where('owner_id', '=', $ownerId);
        return $q;
    }

    /**
     * Get service by ID
     * @param $id
     * @param $ownerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getServiceQuery($id, $ownerId){
        return ManagedService::query()
            ->where('id', $id)
            ->where('owner_id', $ownerId)
            ->with(['solutions']);
    }

    /**
     * Creates a new host from the specification.
     * @param $params
     * @param $ownerId
     * @return ManagedService
     */
    public function add($params, $ownerId){
        // Soft delete query
        $trashedElem = $this->getByName($params['svc_name'], $ownerId)->onlyTrashed()->first();
        if (!empty($trashedElem)){
            $trashedElem->restore();
            return $trashedElem;
        }

        // Insert a new record
        unset($params['id']);
        $item = new ManagedService(array_merge($params, [
            'owner_id' => $ownerId
        ]));
        $item->save();
        return $item;
    }
}

