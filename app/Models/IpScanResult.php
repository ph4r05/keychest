<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 07.09.17
 * Time: 20:17
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class IpScanResult extends Model
{
    const TABLE = 'ip_scan_result';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return array('created_at', 'updated_at', 'finished_at', 'last_scan_at');
    }

    /**
     * Watch service
     */
    public function ipScanRecord()
    {
        return $this->belongsTo('App\Models\IpScanRecord', 'ip_scan_record_id');
    }
}
