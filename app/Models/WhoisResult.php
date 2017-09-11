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

class WhoisResult extends Model
{
    const TABLE = 'whois_result';

    public $incrementing = true;

    protected $guarded = array();

    protected $table = self::TABLE;

    /**
     * Get the top domain record for this result
     */
    public function domain()
    {
        return $this->belongsTo('App\Models\BaseDomain', 'domain_id');
    }

    /**
     * Get the watch_id record for this result
     */
    public function watch_target()
    {
        return $this->belongsTo('App\Models\WatchTarget', 'watch_id');
    }

}
