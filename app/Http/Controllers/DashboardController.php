<?php

/*
 * Dashboard controller, loading data.
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\WatchAssoc;
use App\Models\WatchTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Class DashboardController
 * @package App\Http\Controllers
 */
class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function loadActiveCerts()
    {
        $curUser = Auth::user();
        $userId = $curUser->getAuthIdentifier();

        $wq = $this->getActiveWatcher($userId);
        $activeWatches = $wq->get();
        $activeWatchesIds = $activeWatches->pluck('wid');
        Log::info('Active watches ids: ' . var_export($activeWatchesIds->all(), true));

        // TODO: load all newest DNS scans for active watches
        // determine primary IP addresses
        // load latest TLS scans for active watchers for primary IP addresses.
        //
    }

    /**
     * Returns the query builder for the active watchers for the user id.
     * @param $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getActiveWatcher($userId){
        $watchTbl = (new WatchTarget())->getTable();
        $watchAssocTbl = (new WatchAssoc())->getTable();

        $query = WatchAssoc::query()
            ->join($watchTbl, $watchTbl.'.id', '=', $watchAssocTbl.'.watch_id')
            ->select($watchTbl.'.*', $watchAssocTbl.'.*', $watchTbl.'.id as wid' )
            ->where($watchAssocTbl.'.user_id', '=', $userId)
            ->whereNull($watchAssocTbl.'.deleted_at');
        return $query;
    }


}