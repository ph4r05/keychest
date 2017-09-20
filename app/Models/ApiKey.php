<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 26.05.17
 * Time: 14:31
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * API key access.
 *
 * Class ApiKeys
 * @package App\Models
 */
class ApiKey extends Model
{
    const TABLE = 'api_keys';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return ['created_at', 'updated_at', 'last_seen_active_at', 'verified_at', 'revoked_at'];
    }

    public function user(){
        return $this->belongsTo('App\User', 'user_id');
    }
}
