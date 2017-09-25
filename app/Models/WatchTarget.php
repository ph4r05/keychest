<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class WatchTarget extends Model
{
    const TABLE = 'watch_target';

    public $incrementing = true;

    protected $guarded = array();

    protected $table = self::TABLE;

    protected $hidden = ['assoc'];

    protected $assoc = null;

    public function getDates()
    {
        return array('created_at', 'updated_at', 'last_scan_at');
    }

    /**
     * Get the user that owns the watch record.
     * @deprecated - converted to many-to-many relationship.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
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
            'App\Models\User',
            'user_watch_target',
            'watch_id',
            'user_id');
    }

    /**
     * Optional watch service
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo('App\Models\WatchService', 'service_id');
    }

    /**
     * Optional watch service
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lastDnsScan()
    {
        return $this->belongsTo('App\Models\DnsResult', 'last_dns_scan_id');
    }

    /**
     * Optional IP scan record assignment
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ipScan()
    {
        return $this->belongsTo('App\Models\IpScanRecord', 'ip_scan_id');
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
