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
use App\Models\UserIpScanRecord;
use App\Models\WatchAssoc;
use App\Models\WatchTarget;
use App\User;
use Exception;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Factory as FactoryContract;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Keychest\Services\Exceptions\CouldNotCreateException;

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
     * @param bool $withAll if true, service, lastResult and watchTarget are co-fetched
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function getRecords($userId=null, $withAll=true){
        $q = IpScanRecord::query()
            ->join(UserIpScanRecord::TABLE, function(JoinClause $query) use ($userId) {
                $query->on('ip_scan_record_id', '=', IpScanRecord::TABLE.'.id')
                    ->where('user_id', '=', $userId)
                    ->whereNull('deleted_at')
                    ->whereNull('disabled_at');
            });

        if ($withAll) {
            $q = $q->with(['service', 'lastResult', 'watchTarget']);
        }

        return $q;
    }

    /**
     * Returns number of records already used by the user
     * @param $userId
     * @return int
     */
    public function numRecordsUsed($userId){
        return $this->getRecords($userId, false)->get()->count();
    }

    /**
     * Generates query for record fetch from criteria
     * @param null $criteria
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function fetchRecord($criteria=null){
        $q = IpScanRecord::query();

        if (!empty($criteria)) {
            foreach ($criteria as $key => $val) {
                $q = $q->where($key, $val);
            }
        }

        return $q->with(['service', 'lastResult']);
    }

    /**
     * Tries to fetch the record or creates a new one
     * @param null $criteria
     * @param int $attempts
     * @return array
     * @throws CouldNotCreateException
     * @throws Exception
     */
    public function fetchOrCreateRecord($criteria=null, $attempts=3){
        for($attempt = 0; $attempt < $attempts; $attempt++){
            // fetch attempt
            $q = $this->fetchRecord($criteria);
            $res = $q->first();
            if ($res){
                return [$res, 0];
            }

            // insert attempt
            $newArr = array_merge([
                'created_at' => Carbon::now()
            ], $criteria);

            try {
                return [IpScanRecord::create($newArr), 1];

            } catch(Exception $e){
                if ($attempt+1 >= $attempts){
                    throw $e;
                }
            }
        }

        throw new CouldNotCreateException('Too many attempts');
    }
}
