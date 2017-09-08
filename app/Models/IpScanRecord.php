<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 07.09.17
 * Time: 20:17
 */

namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class IpScanRecord extends Model
{
    protected $guarded = array();

    protected $table = 'ip_scan_record';

    public function getDates()
    {
        return array('created_at', 'updated_at', 'last_scan_at');
    }

    /**
     * Watch service
     */
    public function service()
    {
        return $this->belongsTo('App\Models\WatchService', 'service_id');
    }

    /**
     * Associated users
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\User',
            'user_ip_scan_record',
            'ip_scan_record_id',
            'user_id')->using('App\Models\UserIpScanRecord');
    }

    /**
     * Last scan result
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastResult()
    {
        return $this->hasOne('App\Models\IpScanResult', 'ip_scan_record_id');
    }

    /**
     * Watch target generated by the scan
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function watchTarget()
    {
        return $this->hasOne('App\Models\WatchTarget', 'ip_scan_id');
    }

}
