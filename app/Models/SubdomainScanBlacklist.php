<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SubdomainScanBlacklist extends Model
{
    const TABLE = 'subdomain_scan_blacklist';

    public $incrementing = true;

    protected $guarded = array();

    protected $table = self::TABLE;

    protected $assoc = null;

    public function getDates()
    {
        return array('created_at', 'updated_at', 'detection_first_at', 'detection_last_at');
    }

}
