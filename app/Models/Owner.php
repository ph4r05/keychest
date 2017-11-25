<?php

namespace App\Models;

use App\Models\ApiKey;
use App\Models\WatchTarget;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Owner extends Authenticatable
{
    const TABLE = 'owners';

    use Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Carbon converted date fields
     * @return array
     */
    public function getDates()
    {
        return array('created_at', 'updated_at');
    }

    /**
     * Associated watch targets
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function watchTargets()
    {
        return $this->belongsToMany('App\Models\WatchTarget',
            null,
            'owner_id',
            'watch_id')->using('App\Models\OwnerWatchTarget');
    }

    /**
     * Active watches only
     * @return mixed
     */
    public function activeWatchTargets(){
        return $this->watchTargets()
            ->addSelect(WatchTarget::TABLE . '.*')
            ->addSelect(WatchTarget::TABLE . '.id as watch_id')
            ->whereNull('deleted_at')
            ->whereNull('disabled_at');
    }

    /**
     * Associated IP scan records
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ipScanRecords()
    {
        return $this->belongsToMany('App\Models\IpScanRecord')->using('App\Models\OwnerIpScanRecord');
    }
}
