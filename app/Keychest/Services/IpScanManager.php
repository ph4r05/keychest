<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;

use App\Models\DnsResult;
use App\Models\IpScanRecord;
use App\Models\WatchAssoc;
use App\Models\WatchTarget;
use App\User;
use Exception;
use Illuminate\Contracts\Auth\Factory as FactoryContract;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IpScanManager {

    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * Create a new Auth manager instance.
     *
     * @param  Application  $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Returns list of the records
     * @param null|int $userId
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function getRecords($userId=null){
        $q = IpScanRecord::query()->whereHas('users', function($query) use ($userId) {
            $query->where('user_id', '=' , $userId)
                ->whereNull('deleted_at')
                ->whereNull('disabled_at');
        });
        return $q;
    }
}
