<?php

namespace App;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class ScanJob extends Model
{
    public $incrementing = false;

    protected $guarded = array();

    /**
     * Get the user that owns the phone.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
