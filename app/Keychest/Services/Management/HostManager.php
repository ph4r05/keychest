<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 16.08.17
 * Time: 18:16
 */

namespace App\Keychest\Services\Management;

use App\Http\Request\ParamRequest;
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
use App\Models\SshKey;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use phpseclib\Crypt\RSA;
use Webpatser\Uuid\Uuid;


class HostManager
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
    public function loadHostListQuery($ownerId=null){
        $query = ManagedHost::query();
        if ($ownerId){
            $query = $query->where('owner_id', '=', $ownerId);
        }

        return $query;
    }

    /**
     * Get host by ID
     * @param $id
     * @param $ownerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getHostQuery($id, $ownerId){
        return ManagedHost::query()
            ->where('id', $id)
            ->where('owner_id', $ownerId)
            ->with(['sshKey', 'groups']);
    }

    /**
     * Creates a new host from the specification.
     * @param $hostSpec
     * @param $user
     * @return ManagedHost
     */
    public function addHost(HostDbSpec $hostSpec, User $user){
        // Soft delete query
        $trashedElem = $this->getHostBySpecQuery($hostSpec, $user)->onlyTrashed()->first();
        if (!empty($trashedElem)){
            $trashedElem->restore();
            return $trashedElem;
        }

        // Insert a new host record
        $host = new ManagedHost([
            'host_name' => $hostSpec->getName(),
            'host_addr' => $hostSpec->getAddress(),
            'ssh_port' => !empty($hostSpec->getPort()) ? $hostSpec->getPort() : 22,
            'owner_id' => $user->primary_owner_id
        ]);
        $host->save();
        return $host;
    }

    /**
     * Loads host by defined specs
     * @param HostDbSpec $hostSpec
     * @param User|null $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getHostBySpecQuery(HostDbSpec $hostSpec, User $user=null){
        $q = ManagedHost::query()
            ->where('host_addr', '=', $hostSpec->getAddress())
            ->where('ssh_port', '=', !empty($hostSpec->getPort()) ? $hostSpec->getPort() : 22);

        if (empty($hostSpec->getAgent())){
            $q = $q->whereNull('agent_id');
        } else {
            $q = $q->where('agent_id', '=', $hostSpec->getAgent());
        }

        if (!empty($user)){
            $q = $q->where('owner_id', '=', $user->primary_owner_id);
        }

        $q = $q->with(['sshKey']);
        return $q;
    }
}
