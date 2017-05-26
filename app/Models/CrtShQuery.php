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

class CrtShQuery extends Model
{
    public $incrementing = false;

    protected $guarded = array();

    protected $table = 'crtsh_query';
}
