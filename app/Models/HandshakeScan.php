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

class HandshakeScan extends Model
{
    public $incrementing = true;

    protected $guarded = array();

    protected $table = 'scan_handshakes';

    /**
     * Get the watch_id record for this result
     */
    public function watch_target()
    {
        return $this->belongsTo('App\Model\WatchTarget', 'watch_id');
    }
}
