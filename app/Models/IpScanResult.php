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

class IpScanResult extends Model
{
    protected $guarded = array();

    protected $table = 'ip_scan_result';

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
