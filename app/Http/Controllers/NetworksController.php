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


}
