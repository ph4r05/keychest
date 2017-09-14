<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 22.08.17
 * Time: 14:03
 */
class UserLoginHistory extends Model
{
    const TABLE = 'user_login_history';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return array('login_at');
    }

    /**
     * Get the user that owns the watch record.
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }


}
