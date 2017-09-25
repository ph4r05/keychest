<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ScanJob extends Model
{
    public $incrementing = true;

    protected $guarded = array();

    /**
     * Get the user that owns the phone.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
