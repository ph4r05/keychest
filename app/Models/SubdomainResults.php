<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SubdomainResults extends Model
{
    const TABLE = 'subdomain_results';

    public $incrementing = true;

    protected $guarded = array();

    protected $table = self::TABLE;

    protected $hidden = ['assoc'];

    protected $assoc = null;

    /**
     * Get the corresponding subdomain record
     */
    public function watch()
    {
        return $this->belongsTo('App\Models\SubdomainWatchTarget', 'watch_id');
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
