<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class SubdomainWatchAssoc extends Model
{
    // use SoftDeletes;

    const TABLE = 'owner_subdomain_watch_target';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return array('created_at', 'updated_at', 'deleted_at', 'disabled_at');
    }
}
