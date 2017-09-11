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
    const TABLE = 'crtsh_query_results';

    public $incrementing = true;

    protected $guarded = array();

    protected $table = self::TABLE;
}
