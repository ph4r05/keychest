<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 07.09.17
 * Time: 20:25
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;


class ManagedSolutionToServicePivot extends Pivot
{
    // use SoftDeletes;

    protected $guarded = array();

    protected $table = ManagedSolutionToService::TABLE;

    protected $foreignKey = 'solution_id';

    protected $relatedKey = 'service_id';

    public function getDates()
    {
        return array('created_at', 'updated_at', 'deleted_at');
    }
}

