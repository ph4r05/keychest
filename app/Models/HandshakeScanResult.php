<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 26.05.17
 * Time: 14:31
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class HandshakeScanResult extends Model
{
    const TABLE = 'scan_handshake_results';

    public $incrementing = true;

    protected $guarded = array();

    protected $table = self::TABLE;
}
