<?php

/*
 * Taken from
 * https://github.com/laravel/framework/blob/5.3/src/Illuminate/Auth/Console/stubs/make/controllers/HomeController.stub
 */

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Request\ParamRequest;
use App\Http\Requests;
use App\Keychest\Services\CredentialsManager;
use App\Keychest\Services\Management\HostDbSpec;
use App\Keychest\Services\Management\HostGroupManager;
use App\Keychest\Services\Management\HostManager;
use App\Keychest\Services\Management\MgmtServiceManager;
use App\Keychest\Utils\DataTools;
use App\Keychest\Utils\DbTools;
use App\Keychest\Utils\DomainTools;
use App\Models\ManagedHostGroup;
use App\Rules\HostSpecObjRule;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;


/**
 * Class MgmtServiceController
 * @package App\Http\Controllers\Management
 */
class MgmtServiceController extends Controller
{
    /**
     * @var HostManager
     */
    protected $hostManager;

    /**
     * @var HostGroupManager
     */
    protected $hostGroupManager;

    /**
     * @var MgmtServiceManager
     */
    protected $serviceManager;

    /**
     * @var CredentialsManager
     */
    protected $credentialsManager;

    /**
     * Create a new controller instance.
     * @param HostManager $hostManager
     * @param HostGroupManager $hostGroupManager
     * @param MgmtServiceManager $serviceManager
     * @param CredentialsManager $credentialsManager
     */
    public function __construct(HostManager $hostManager,
                                HostGroupManager $hostGroupManager,
                                MgmtServiceManager $serviceManager,
                                CredentialsManager $credentialsManager)
    {
        $this->middleware('auth');
        $this->hostManager = $hostManager;
        $this->hostGroupManager = $hostGroupManager;
        $this->serviceManager = $serviceManager;
        $this->credentialsManager = $credentialsManager;
    }

}
