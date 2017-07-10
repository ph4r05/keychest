<?php

namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class SubdomainScanBlacklist extends Model
{
    public $incrementing = true;

    protected $guarded = array();

    protected $table = 'subdomain_scan_blacklist';

    protected $assoc = null;

    public function getDates()
    {
        return array('created_at', 'updated_at', 'detection_first_at', 'detection_last_at');
    }

}
