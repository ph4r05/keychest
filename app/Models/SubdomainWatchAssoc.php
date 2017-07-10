<?php

namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class SubdomainWatchAssoc extends Model
{
    public $incrementing = true;

    protected $guarded = array();

    protected $table = 'user_subdomain_watch_target';

    public function getDates()
    {
        return array('created_at', 'updated_at', 'deleted_at', 'disabled_at');
    }
}
