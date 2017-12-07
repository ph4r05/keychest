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
use App\Keychest\Utils\DataTools;
use App\Keychest\Utils\DbTools;
use App\Keychest\Utils\DomainTools;
use App\Rules\HostSpecObjRule;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;


/**
 * Class HostGroupController
 * @package App\Http\Controllers\Management
 */
class HostGroupController extends Controller
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
     * @var CredentialsManager
     */
    protected $credentialsManager;

    /**
     * Create a new controller instance.
     * @param HostManager $hostManager
     * @param HostGroupManager $hostGroupManager
     * @param CredentialsManager $credentialsManager
     */
    public function __construct(HostManager $hostManager,
                                HostGroupManager $hostGroupManager,
                                CredentialsManager $credentialsManager)
    {
        $this->middleware('auth');
        $this->hostManager = $hostManager;
        $this->hostGroupManager = $hostGroupManager;
        $this->credentialsManager = $credentialsManager;
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
        $noHostGroups = Input::get('noHostGroups');

        // Host Db spec for storage.
        $user = Auth::getUser();
        $query = $this->hostGroupManager->searchHostGroupQuery($user->primary_owner_id, $q);

        if ($noHostGroups){
            $query = $query->where('group_name', 'not like', 'host-%');
        }

        $results = $query->limit(30)->get();

        return response()->json([
            'state' => 'success',
            'results' => $results,
        ], 200);
    }

}
