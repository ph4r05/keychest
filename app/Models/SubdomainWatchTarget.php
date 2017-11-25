<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SubdomainWatchTarget extends Model
{
    const TABLE = 'subdomain_watch_target';

    public $incrementing = true;

    protected $guarded = array();

    protected $table = self::TABLE;

    protected $hidden = ['assoc'];

    protected $assoc = null;

    /**
     * Gets the top domain associated with Whois check
     */
    public function topDomain()
    {
        return $this->belongsTo('App\Models\BaseDomain', 'top_domain_id');
    }

    /**
     * Watch targets that belongs to the user
     */
    public function owners()
    {
        return $this->belongsToMany(
            'App\Models\Owner',
            'owner_watch_target',
            'watch_id',
            'owner_id');
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
