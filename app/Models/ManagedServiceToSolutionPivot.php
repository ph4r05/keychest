<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 07.09.17
 * Time: 20:25
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;


class ManagedServiceToSolutionPivot extends Pivot
{
    // use SoftDeletes;

    protected $guarded = array();

    protected $table = ManagedServiceToSolution::TABLE;

    protected $foreignKey = 'service_id';

    protected $relatedKey = 'solution_id';

    public function getDates()
    {
        return array('created_at', 'updated_at', 'deleted_at');
    }
}

