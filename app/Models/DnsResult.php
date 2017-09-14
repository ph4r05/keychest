<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 26.05.17
 * Time: 14:31
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class DnsResult extends Model
{
    const TABLE = 'scan_dns';

    public $incrementing = true;

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return array('created_at', 'updated_at', 'last_scan_at');
    }

    /**
     * Get the watch_id record for this result
     */
    public function watch_target()
    {
        return $this->belongsTo('App\Models\WatchTarget', 'watch_id');
    }

    /**
     * DNS scan entries
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entries(){
        return $this->hasMany('\App\Model\DnsEntry', 'scan_id');
    }
}
