<?php

namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class WatchTarget extends Model
{
    public $incrementing = true;

    protected $guarded = array();

    protected $table = 'watch_target';

    /**
     * Get the user that owns the phone.
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
