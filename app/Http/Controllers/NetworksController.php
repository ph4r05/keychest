<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Keychest\Services\IpScanManager;
use App\Keychest\Services\ScanManager;
use App\Keychest\Services\ServerManager;
use App\Keychest\Utils\DataTools;
use App\Keychest\Utils\DbTools;
use App\Keychest\Utils\DomainTools;
use App\Models\DnsEntry;
use App\Models\DnsResult;
use App\Models\HandshakeScan;
use App\Models\WatchAssoc;
use App\Models\WatchTarget;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;

/**
 * Class NetworksController
 * @package App\Http\Controllers
 */
class NetworksController extends Controller
{
    /**
     * @var ServerManager
     */
    protected $serverManager;

    /**
     * Scan manager
     * @var ScanManager
     */
    protected $scanManager;
    /**
     * Scan manager
     * @var IpScanManager
     */
    protected $ipScanManager;

    /**
     * Create a new controller instance.
     * @param ServerManager $serverManager
     * @param ScanManager $scanManager
     * @param IpScanManager $ipScanManager
     */
    public function __construct(ServerManager $serverManager, ScanManager $scanManager, IpScanManager $ipScanManager)
    {
        $this->serverManager = $serverManager;
        $this->scanManager = $scanManager;
        $this->ipScanManager = $ipScanManager;
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        return view('ipservers');
    }

    /**
     * Loads all scan definitions for the current user
     * @return \Illuminate\Http\JsonResponse
     */
    public function ipScanList(){
        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();

        $sort = strtolower(trim(Input::get('sort')));
        $filter = strtolower(trim(Input::get('filter')));
        $per_page = intval(trim(Input::get('per_page')));
        $sort_parsed = DataTools::vueSortToDb($sort);

        $watchTbl = (new WatchTarget())->getTable();
        $watchAssocTbl = (new WatchAssoc())->getTable();

        $query = $this->ipScanManager->getRecords($userId);
        if (!empty($filter)){
            $query = $query->where('service_name', 'like', '%' . $filter . '%');
        }

        // sorting
        $sort_parsed->transform(function($item, $key) use ($watchAssocTbl){
            return (!in_array($item[0], ['created_at', 'updated_at'])) ?
                $item : [$watchAssocTbl.'.'.$item[0], $item[1]];
        });

        $query = DbTools::sortQuery($query, $sort_parsed);

        $ret = $query->paginate($per_page > 0  && $per_page < 1000 ? $per_page : 100); // type: \Illuminate\Pagination\LengthAwarePaginator
        return response()->json($ret, 200);
    }

}
