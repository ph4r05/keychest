<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 25.10.17
 * Time: 14:39
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ManagedHostGroup extends Model
{
    const TABLE = 'managed_host_groups';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return array('created_at', 'updated_at', 'deleted_at');
    }

    /**
     * Get the user that owns the phone.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
