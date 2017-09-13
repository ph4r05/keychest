<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;

use App\Keychest\Utils\IpRange;
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
                $query->on('ip_scan_record_id', '=', IpScanRecord::TABLE.'.id');

                if (!empty($userId)){
                    $query->where('user_id', '=', $userId);
                }

                $query->whereNull('deleted_at')
                    ->whereNull('disabled_at');
            })
            ->select([
                IpScanRecord::TABLE . '.*',
                UserIpScanRecord::TABLE . '.*',
                IpScanRecord::TABLE . '.id AS record_id',
                UserIpScanRecord::TABLE . '.id as assoc_id',
                UserIpScanRecord::TABLE . '.id as id'
            ]);

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
     * @param null $onAddFnc
     * @param int $attempts
     * @return array
     * @throws CouldNotCreateException
     * @throws Exception
     */
    public function fetchOrCreateRecord($criteria=null, $onAddFnc=null, $attempts=3){
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

            if (!empty($onAddFnc)){
                /** @var callable $onAddFnc */
                $newArr = $onAddFnc($newArr);
            }

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

    /**
     * Returns matches that intersect the criteria for the given user
     * @param $criteria
     * @param null $userId
     * @param bool $withAll
     * @return Collection
     */
    public function hasScanIntersection($criteria, $userId=null, $withAll=false){
        $q = $this->getRecords($userId, $withAll);

        $criteria_fields = ['service_name', 'service_port'];
        foreach ($criteria_fields as $fld){
            if (!isset($criteria[$fld])){
                continue;
            }
            $q->where($fld, '=', $criteria[$fld]);
        }

        $matches = $q->get();
        if ($matches->isEmpty()){
            return collect();
        }

        // Find IP intersections
        $curRange = new IpRange($criteria['ip_beg'], $criteria['ip_end']);
        return $matches->filter(function($value, $key) use ($curRange) {
            return $curRange->hasIntersection(new IpRange($value['ip_beg'], $value['ip_end']));
        });
    }

    /**
     * Builds criteria array for the IP range
     * @param null $server
     * @param null $port
     * @param null $beg_ip
     * @param null $end_ip
     * @return array
     */
    public static function ipScanRangeCriteria($server=null, $port=null, $beg_ip=null, $end_ip=null){
        return [
            'service_name' => $server,
            'service_port' => empty($port) ? 443 : $port,
            'ip_beg' => $beg_ip,
            'ip_end' => $end_ip
        ];
    }
}
