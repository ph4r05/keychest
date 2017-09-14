<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class WatchAssoc extends Model
{
    // use SoftDeletes;

    const TABLE = 'user_watch_target';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return array('created_at', 'updated_at', 'deleted_at', 'disabled_at', 'auto_scan_added_at');
    }
}
