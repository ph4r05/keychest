<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 26.05.17
 * Time: 14:31
 */

namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class DnsResult extends Model
{
    public $incrementing = true;

    protected $guarded = array();

    protected $table = 'scan_dns';

    /**
     * Get the watch_id record for this result
     */
    public function watch_target()
    {
        return $this->belongsTo('App\Model\WatchTarget', 'watch_id');
    }

    public function getDates()
    {
        return array('created_at', 'updated_at', 'last_scan_at');
    }
}
