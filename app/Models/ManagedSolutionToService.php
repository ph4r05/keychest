<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 07.09.17
 * Time: 20:25
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ManagedSolutionToService extends Model
{
    // use SoftDeletes;

    const TABLE = 'managed_solution_to_service';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return array('created_at', 'updated_at', 'deleted_at');
    }
}

