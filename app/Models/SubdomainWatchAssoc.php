<?php

namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubdomainWatchAssoc extends Model
{
    use SoftDeletes;

    const TABLE = 'user_subdomain_watch_target';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return array('created_at', 'updated_at', 'deleted_at', 'disabled_at');
    }
}
