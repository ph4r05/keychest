<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 16.08.17
 * Time: 18:16
 */

namespace App\Keychest\Services\Management;

use App\Keychest\DataClasses\GeneratedSshKey;
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
     * Creates a new host from the specification.
     * @param $hostSpec
     * @param $user
     * @return ManagedHost
     */
    public function addHost(HostDbSpec $hostSpec, User $user){
        $host = new ManagedHost([
            'host_name' => $hostSpec->getName(),
            'host_addr' => $hostSpec->getAddress(),
            'ssh_port' => $hostSpec->getPort(),
            'user_id' => $user->id
        ]);
        $host->save();
        return $host;
    }

    public function getHostBySpecQuery(HostDbSpec $hostSpec, User $user=null){

    }
}
