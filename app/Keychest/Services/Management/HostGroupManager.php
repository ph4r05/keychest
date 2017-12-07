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
     * @return bool
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
}

