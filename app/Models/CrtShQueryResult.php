<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 26.05.17
 * Time: 14:32
 */

namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class CrtShQueryResult extends Model
{
    public $incrementing = true;

    protected $guarded = array();

    protected $table = 'crtsh_query_results';
}
