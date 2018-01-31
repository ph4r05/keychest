<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 25.10.17
 * Time: 14:39
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ManagedSecGroup extends Model
{
    const TABLE = 'managed_security_groups';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return array('created_at', 'updated_at', 'deleted_at');
    }

    /**
     * Get the user that owns the phone.
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\Owner', 'owner_id');
    }

    /**
     * Associated hosts
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany('App\Models\ManagedService',
            ManagedServiceToSecGroup::TABLE,
            'group_id',
            'service_id')
            ->withTimestamps()
            ->using('App\Models\ManagedServiceToSecGroupPivot');
    }
}
