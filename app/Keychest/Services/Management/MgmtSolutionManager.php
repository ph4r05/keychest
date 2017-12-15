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
use App\Models\ManagedSolution;
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


class MgmtSolutionManager
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
     * Builds query to load solution list.
     *
     * @param null $ownerId
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function loadListQuery($ownerId=null){
        $query = ManagedSolution::query();
        if ($ownerId){
            $query = $query->where('owner_id', '=', $ownerId);
        }

        return $query;
    }

    /**
     * Loads host by defined specs
     * @param $name
     * @param $ownerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getByName($name, $ownerId){
        $q = ManagedSolution::query()
            ->where('sol_name', '=', $name);
        $q = $q->where('owner_id', '=', $ownerId);
        return $q;
    }

    /**
     * Get solution by ID
     * @param $id
     * @param $ownerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQuery($id, $ownerId){
        return ManagedSolution::query()
            ->where('id', $id)
            ->where('owner_id', $ownerId)
            ->with(['services']);
    }

    /**
     * Creates a new solution from the specification.
     * @param $params
     * @param $ownerId
     * @return ManagedSolution
     */
    public function add($params, $ownerId){
        // Soft delete query
        $trashedElem = $this->getByName($params['sol_name'], $ownerId)->onlyTrashed()->first();
        if (!empty($trashedElem)){
            $trashedElem->restore();
            return $trashedElem;
        }

        // Insert a new record
        unset($params['id']);
        $item = new ManagedSolution(array_merge($params, [
            'owner_id' => $ownerId
        ]));
        $item->save();
        return $item;
    }

    /**
     * Query for group search / autocomplete
     * @param $ownerId
     * @param $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function searchQuery($ownerId, $search){
        return ManagedSolution::query()
            ->where('owner_id', '=', $ownerId)
            ->where('sol_name', 'like', '%' . $search . '%');
    }
}

