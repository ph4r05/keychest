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
use App\Keychest\Services\Management\MgmtSolutionManager;
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
 * Class MgmtSolutionController
 * @package App\Http\Controllers\Management
 */
class MgmtSolutionController extends Controller
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
     * @var MgmtSolutionManager
     */
    protected $solutionManager;

    /**
     * @var CredentialsManager
     */
    protected $credentialsManager;

    /**
     * Create a new controller instance.
     * @param HostManager $hostManager
     * @param HostGroupManager $hostGroupManager
     * @param MgmtSolutionManager $solutionManager
     * @param CredentialsManager $credentialsManager
     */
    public function __construct(HostManager $hostManager,
                                HostGroupManager $hostGroupManager,
                                MgmtSolutionManager $solutionManager,
                                CredentialsManager $credentialsManager)
    {
        $this->middleware('auth');
        $this->hostManager = $hostManager;
        $this->hostGroupManager = $hostGroupManager;
        $this->solutionManager = $solutionManager;
        $this->credentialsManager = $credentialsManager;
    }

    /**
     * Get all managed solutions
     */
    public function getSolutions(){
        $curUser = Auth::user();
        $ownerId = $curUser->primary_owner_id;

        $sort = strtolower(trim(Input::get('sort')));
        $filter = strtolower(trim(Input::get('filter')));
        $per_page = intval(trim(Input::get('per_page')));
        $return_all = intval(trim(Input::get('return_all')));
        $sort_parsed = DataTools::vueSortToDb($sort);

        // server list load
        $query = $this->solutionManager->loadListQuery($ownerId);
        if (!empty($filter)){
            $query = $query->where(function(Builder $query) use($filter) {
                $query->where('sol_name', 'like', '%'. $filter . '%')
                    ->orWhere('sol_display', 'like', '%'. $filter . '%');
            });
        }

        $query = $query->with(['services']);
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
     * Get solution for the user
     * @param ParamRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSolution(ParamRequest $request, $id)
    {
        $user = Auth::getUser();
        $sol = $this->solutionManager->getQuery($id, $user->primary_owner_id)->first();

        return response()->json([
            'state' => $sol ? 'success' : 'not-found',
            'record' => $sol,
        ], $sol ? 200 : 404);
    }

    /**
     * Adds managed solution
     *
     * @param ParamRequest $request
     * @return Response
     * @throws Exception
     */
    public function addSolution(ParamRequest $request)
    {
        $this->validate($request, [  // TODO: refine validation rules
            'sol_name' => 'required|max:160',
            'sol_display' => 'present|max:160',
            'sol_type' => 'required|max:64',
            'sol_criticality' => 'required|integer',
            'sol_assurance' => 'required|max:64',
        ], [], [
                'sol_name' => 'Solution code']
        );

        // Host Db spec for storage.
        $user = Auth::getUser();
        $ownerId = $user->primary_owner_id;
        $name = Input::get('sol_name');

        // Duplicity detection
        $exists = $this->solutionManager->getByName($name, $ownerId)->first();
        if (!empty($exists)){
            return response()->json(['status' => 'already-present'], 410);
        }

        $params = [
            'sol_name' => $name,
            'sol_display' => Input::get('sol_display'),
            'sol_type' => Input::get('sol_type'),
            'sol_criticality' => Input::get('sol_criticality'),
            'sol_assurance_level' => Input::get('sol_assurance'),
        ];

        // Add
        $dbRecord = $this->solutionManager->add($params, $ownerId);

        return response()->json([
            'state' => 'success',
            'record' => $dbRecord,
        ], 200);
    }

}
