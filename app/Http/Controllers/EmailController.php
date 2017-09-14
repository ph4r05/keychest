<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Jobs\AutoAddSubsJob;
use App\Keychest\Services\EmailManager;
use App\Keychest\Services\ServerManager;
use App\Keychest\Services\SubdomainManager;
use App\Keychest\Utils\DataTools;
use App\Keychest\Utils\DbTools;
use App\Keychest\Utils\DomainTools;
use App\Models\LastScanCache;
use App\Models\SubdomainResults;
use App\Models\SubdomainWatchAssoc;
use App\Models\SubdomainWatchTarget;
use App\Models\WatchAssoc;
use App\Models\WatchTarget;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;

/**
 * Class EmailController
 * @package App\Http\Controllers
 */
class EmailController extends Controller
{
    /**
     * @var EmailManager
     */
    protected $manager;

    /**
     * Create a new controller instance.
     * @param EmailManager $manager
     */
    public function __construct(EmailManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Unsubscribe from email report
     * @return $this
     */
    public function unsubscribe($token){
        $res = $this->manager->unsubscribe($token);

        return view('unsubscribe')->with(
            ['token' => $token, 'res' => $res]
        );
    }
}
