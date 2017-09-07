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

class CrtShQuery extends Model
{
    public $incrementing = true;

    protected $guarded = array();

    protected $table = 'crtsh_query';

    /**
     * Get the watch_id record for this result
     */
    public function watch_target()
    {
        return $this->belongsTo('App\Model\WatchTarget', 'watch_id');
    }

    /**
     * Optional watch service
     */
    public function service()
    {
        return $this->belongsTo('App\WatchService', 'service_id');
    }
}
