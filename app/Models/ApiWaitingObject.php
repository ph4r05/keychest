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
 * Object waiting to be processed by the backend and eventually added to the Keychest.
 * Action started by client using an API key.
 *
 * This may be e.g. request to add a new certificate to the system (add all related watches).
 *
 * Class ApiWaitingObject
 * @package App\Models
 */
class ApiWaitingObject extends Model
{
    const TABLE = 'api_waiting';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return ['created_at', 'updated_at', 'last_scan_at', 'ct_found_at', 'processed_at', 'finished_at'];
    }

    /**
     * Associated user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function apiKey(){
        return $this->belongsTo('App\Models\ApiKey', 'api_key_id');
    }
}
