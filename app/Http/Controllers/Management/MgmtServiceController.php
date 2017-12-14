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

    /**
     * Get all managed services
     */
    public function getServices(){
        $curUser = Auth::user();
        $ownerId = $curUser->primary_owner_id;

        $sort = strtolower(trim(Input::get('sort')));
        $filter = strtolower(trim(Input::get('filter')));
        $per_page = intval(trim(Input::get('per_page')));
        $return_all = intval(trim(Input::get('return_all')));
        $sort_parsed = DataTools::vueSortToDb($sort);

        // server list load
        $query = $this->serviceManager->loadListQuery($ownerId);
        if (!empty($filter)){
            $query = $query->where(function(Builder $query) use($filter) {
                $query->where('svc_name', 'like', '%'. $filter . '%')
                    ->orWhere('svc_display', 'like', '%'. $filter . '%');
            });
        }

        $query = $query->with(['hostGroups']);
        $query = DbTools::sortQuery($query, $sort_parsed);

        $page_size = $per_page > 0 && $per_page < 1000 ? $per_page : 100;
        if ($return_all){
            $page_size = config('keychest.max_servers');
        }

        $ret = $query->paginate($page_size); // type: \Illuminate\Pagination\LengthAwarePaginator
        $retArr = $ret->toArray();

        return response()->json($retArr, 200);
    }

    /**
     * Get service for the user
     * @param ParamRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getService(ParamRequest $request, $id)
    {
        $user = Auth::getUser();
        $svc = $this->serviceManager->getServiceQuery($id, $user->primary_owner_id)->first();

        return response()->json([
            'state' => $svc ? 'success' : 'not-found',
            'record' => $svc,
        ], $svc ? 200 : 404);
    }

    /**
     * Adds managed service
     *
     * @param ParamRequest $request
     * @return Response
     * @throws Exception
     */
    public function addService(ParamRequest $request)
    {
        $this->validate($request, [  // TODO: refine validation rules
            'svc_name' => 'required|max:160',
            'svc_display' => 'present|max:160',
            'svc_provider' => 'present|max:160',
            'svc_deployment' => 'present|max:160',
            'svc_domain_auth' => 'present|max:160',
            'svc_config' => 'present|max:160',
        ], [], [
                'svc_name' => 'Service code']
        );

        // Host Db spec for storage.
        $user = Auth::getUser();
        $ownerId = $user->primary_owner_id;
        $svcName = Input::get('svc_name');

        // Duplicity detection
        $exists = $this->serviceManager->getByName($svcName, $ownerId)->first();
        if (!empty($exists)){
            return response()->json(['status' => 'already-present'], 410);
        }

        $params = [
            'svc_name' => $svcName,
            'svc_display' => Input::get('svc_display'),
            'svc_provider' => Input::get('svc_provider'),
            'svc_deployment' => Input::get('svc_deployment'),
            'svc_domain_auth' => Input::get('svc_domain_auth'),
            'svc_config' => Input::get('svc_config'),
        ];

        // Add
        $dbRecord = $this->serviceManager->add($params, $ownerId);

        return response()->json([
            'state' => 'success',
            'record' => $dbRecord,
        ], 200);
    }

}
