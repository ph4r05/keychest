<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 26.05.17
 * Time: 14:31
 */

namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class WhoisResult extends Model
{
    public $incrementing = true;

    protected $guarded = array();

    protected $table = 'whois_result';

}
