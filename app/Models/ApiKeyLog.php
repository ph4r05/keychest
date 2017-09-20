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
class ApiKeyLog extends Model
{
    const TABLE = 'api_keys_log';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return ['created_at', 'updated_at'];
    }

    public function apiKey(){
        return $this->belongsTo('App\Models\ApiKey', 'api_key_id');
    }
}
