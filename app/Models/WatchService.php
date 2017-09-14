<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 07.09.17
 * Time: 20:09
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class WatchService extends Model
{
    const TABLE = 'watch_service';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return array('created_at', 'updated_at', 'last_scan_at');
    }

    /**
     * Watch targets associated to the service
     */
    public function watchTargets()
    {
        return $this->hasMany(
            'App\Models\WatchTarget',
            'service_id');
    }

}

