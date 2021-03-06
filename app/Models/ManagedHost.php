<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 25.10.17
 * Time: 14:39
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ManagedHost extends Model
{
    use SoftDeletes;

    const TABLE = 'managed_hosts';

    protected $table = self::TABLE;

    protected $guarded = array();

    public function getDates()
    {
        return array('created_at', 'updated_at', 'deleted_at');
    }

    /**
     * Get the user that owns the entity.
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\Owner', 'owner_id');
    }

    /**
     * Ssh key associated to the host.
     */
    public function sshKey()
    {
        return $this->belongsTo('App\Models\SshKey', 'ssh_key_id');
    }

    /**
     * Associated hosts
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany('App\Models\ManagedHostGroup',
            ManagedHostToGroup::TABLE,
            'host_id',
            'group_id')
            ->withTimestamps()
            ->using('App\Models\ManagedHostToGroupPivot');
    }
}
