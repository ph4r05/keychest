<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 01.08.17
 * Time: 21:16
 */

namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class LastScanCache extends Model
{
    const TABLE = 'last_scan_cache';

    public $incrementing = true;

    protected $guarded = array();

    protected $table = self::TABLE;
}
