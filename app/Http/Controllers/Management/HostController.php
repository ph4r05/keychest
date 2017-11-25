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
 * Class HostController
 * @package App\Http\Controllers\Management
 */
class HostController extends Controller
{
    /**
     * @var HostManager
     */
    protected $hostManager;

    /**
     * @var CredentialsManager
     */
    protected $credentialsManager;

    /**
     * Create a new controller instance.
     * @param HostManager $hostManager
     */
    public function __construct(HostManager $hostManager, CredentialsManager $credentialsManager)
    {
        $this->middleware('auth');
        $this->hostManager = $hostManager;
        $this->credentialsManager = $credentialsManager;
    }

    /**
     * Get all managed hosts
     */
    public function getHosts(){
        $curUser = Auth::user();
        $ownerId = $curUser->primary_owner_id;

        $sort = strtolower(trim(Input::get('sort')));
        $filter = strtolower(trim(Input::get('filter')));
        $per_page = intval(trim(Input::get('per_page')));
        $return_all = intval(trim(Input::get('return_all')));
        $sort_parsed = DataTools::vueSortToDb($sort);

        // server list load
        $query = $this->hostManager->loadHostListQuery($ownerId);
        if (!empty($filter)){
            $query = $query->where(function(Builder $query) use($filter) {
                $query->where('host_name', 'like', '%'. $filter . '%')
                    ->orWhere('host_addr', 'like', '%'. $filter . '%');
            });
        }

        $query = $query->with(['sshKey']);
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
     * Show the application dashboard.
     *
     * @param ParamRequest $request
     * @return Response
     */
    public function addHost(ParamRequest $request)
    {
        $hostSpecStr = Input::get('host_addr');

        $hostSpec = DomainTools::tryHostSpecParse($hostSpecStr);
        $request->addExtraParam('host_spec_obj', $hostSpec);

        $this->validate($request, [
            'host_addr' => 'required',
            'host_name' => 'present|max:160',
            'bit_size' => [
                'optional',
                Rule::in([2048, 3072])
            ],
            'host_spec_obj' => new HostSpecObjRule()
        ], [], [
            'host_addr' => 'Host Address',
            'host_spec_obj' => 'Host Address']
        );

        // Host Db spec for storage.
        $user = Auth::getUser();
        $agentId = null;  // TODO: get from req, check permissions on agent_id for the given user
        $hostName = Input::get('host_name');
        $hostDbSpec = new HostDbSpec($hostName, $hostSpec->getAddr(), $hostSpec->getPort(), $agentId);

        // Duplicity detection
        $exHost = $this->hostManager->getHostBySpecQuery($hostDbSpec, $user)->first();
        if (!empty($exHost)){
            return response()->json(['status' => 'already-present'], 410);
        }

        // Add host
        $dbHost = $this->hostManager->addHost($hostDbSpec, $user);

        // Associate SSH credentials
        $sshKey = $dbHost->sshKey;
        if (empty($sshKey)) {
            $bitSize = Input::get('bit_size', 2048);
            $sshKey = $this->credentialsManager->allocateSshKeyToUser($bitSize, $user);
            $dbHost->sshKey()->associate($sshKey);
            $dbHost->save();
        }

        return response()->json([
                'state' => 'success',
                'host_id' => $dbHost->id,
                'ssh_key_uuid' => $sshKey->key_id,
                'ssh_key_public' => $sshKey->pub_key
            ], 200);
    }

    /**
     * Processes result of the load list, removes private key from the listing.
     * @param Collection $col
     * @return mixed
     */
    protected function processListResults($col){
        $col = $col->transform(function($value, $key) {
            if (isset($value['ssh_key'])){
                unset($value['ssh_key']['priv_key']);
            }
            return $value;
        });

        return $col;
    }

}