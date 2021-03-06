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
use App\Keychest\Services\Management\SecGroupManager;
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
 * Class SecGroupController
 * @package App\Http\Controllers\Management
 */
class SecGroupController extends Controller
{
    /**
     * @var HostManager
     */
    protected $hostManager;

    /**
     * @var SecGroupManager
     */
    protected $secGroupManager;

    /**
     * @var CredentialsManager
     */
    protected $credentialsManager;

    /**
     * Create a new controller instance.
     * @param HostManager $hostManager
     * @param SecGroupManager $secGroupManager
     * @param CredentialsManager $credentialsManager
     */
    public function __construct(HostManager $hostManager,
                                SecGroupManager $secGroupManager,
                                CredentialsManager $credentialsManager)
    {
        $this->middleware('auth');
        $this->hostManager = $hostManager;
        $this->secGroupManager = $secGroupManager;
        $this->credentialsManager = $credentialsManager;
    }

    /**
     * Get all groups
     */
    public function getGroups(){
        $curUser = Auth::user();
        $ownerId = $curUser->primary_owner_id;

        $sort = strtolower(trim(Input::get('sort')));
        $filter = strtolower(trim(Input::get('filter')));
        $per_page = intval(trim(Input::get('per_page')));
        $return_all = intval(trim(Input::get('return_all')));
        $sort_parsed = DataTools::vueSortToDb($sort);

        // server list load
        $query = $this->secGroupManager->loadListQuery($ownerId);
        if (!empty($filter)){
            $query = $query->where(function(Builder $query) use($filter) {
                $query->where('sgrp_display', 'like', '%'. $filter . '%')
                    ->orWhere('sgrp_name', 'like', '%'. $filter . '%');
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
        $retArr['data'] = $this->processListResults(collect($retArr['data']));

        return response()->json($retArr, 200);
    }

    /**
     * Get sec group for the user
     * @param ParamRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getService(ParamRequest $request, $id)
    {
        $user = Auth::getUser();
        $res = $this->secGroupManager->getQuery($id, $user->primary_owner_id)->first();

        return response()->json([
            'state' => $res ? 'success' : 'not-found',
            'record' => $res,
        ], $res ? 200 : 404);
    }

    /**
     * Search for the given group.
     *
     * @param ParamRequest $request
     * @return Response
     */
    public function searchGroups(ParamRequest $request)
    {
        $q = Input::get('q');

        // Host Db spec for storage.
        $user = Auth::getUser();
        $query = $this->secGroupManager->searchGroupQuery($user->primary_owner_id, $q);
        $results = $query->limit(100)->get();

        return response()->json([
            'state' => 'success',
            'results' => $results,
        ], 200);
    }

    /**
     * Add a new group
     * @param ParamRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addGroup(ParamRequest $request){
        $this->validate($request, [  // TODO: refine validation rules
            'sgrp_name' => 'required|max:250',
            'sgrp_display' => 'present|max:250',
            'sgrp_criticality' => 'required|integer',
            'sgrp_assurance' => 'required|max:64',
        ], [], [
                'sgrp_code' => 'Risk Group Name']
        );

        // Host Db spec for storage.
        $user = Auth::getUser();
        $ownerId = $user->primary_owner_id;
        $name = Input::get('sol_name');

        // Duplicity detection
        $exists = $this->secGroupManager->getQuery($name, $ownerId)->first();
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

    /**
     * Processes result of the load list
     * @param Collection $col
     * @return mixed
     */
    protected function processListResults($col){
        return $col;
    }

}
