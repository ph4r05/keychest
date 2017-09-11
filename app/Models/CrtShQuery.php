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
    const TABLE = 'crtsh_query';

    public $incrementing = true;

    protected $guarded = array();

    protected $table = self::TABLE;

    /**
     * Get the watch_id record for this result
     */
    public function watch_target()
    {
        return $this->belongsTo('App\Models\WatchTarget', 'watch_id');
    }

    /**
     * Optional watch service
     */
    public function service()
    {
        return $this->belongsTo('App\Models\WatchService', 'service_id');
    }
}
