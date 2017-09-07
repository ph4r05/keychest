<?php

namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class SubdomainResultsEntry extends Model
{
    public $incrementing = true;

    protected $guarded = array();

    protected $table = 'subdomain_watch_result_entry';

    protected $hidden = ['assoc'];

    protected $assoc = null;

    /**
     * Get the corresponding subdomain record
     */
    public function watch()
    {
        return $this->belongsTo('App\Models\SubdomainWatchTarget', 'watch_id');
    }

    /**
     * Optional watch service
     */
    public function service()
    {
        return $this->belongsTo('App\WatchService', 'service_id');
    }

    public function getDates()
    {
        return array('created_at', 'updated_at', 'last_scan_at');
    }

    /**
     * @return null
     */
    public function getAssoc()
    {
        return $this->assoc;
    }

    /**
     * @param null $assoc
     */
    public function setAssoc($assoc)
    {
        $this->assoc = $assoc;
    }


}
