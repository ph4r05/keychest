<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 07.09.17
 * Time: 20:09
 */

namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class WatchService extends Model
{
    protected $guarded = array();

    protected $table = 'watch_service';

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

