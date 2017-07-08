<?php

namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class SubdomainWatchTarget extends Model
{
    public $incrementing = true;

    protected $guarded = array();

    protected $table = 'subdomain_watch_target';

    protected $hidden = ['assoc'];

    protected $assoc = null;

    /**
     * Get the user that owns the watch record.
     * @deprecated - converted to many-to-many relationship.
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

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
    public function users()
    {
        return $this->belongsToMany(
            'App\User',
            'user_watch_target',
            'watch_id',
            'user_id');
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
