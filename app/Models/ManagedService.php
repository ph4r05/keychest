<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 25.10.17
 * Time: 14:39
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ManagedService extends Model
{
    use SoftDeletes;

    const TABLE = 'managed_services';

    protected $table = self::TABLE;

    protected $guarded = array();

    public function getDates()
    {
        return array('created_at', 'updated_at', 'deleted_at');
    }

    /**
     * Get the user that owns the entity.
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\Owner', 'owner_id');
    }

    /**
     * Associated solutions
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function solutions()
    {
        return $this->belongsToMany('App\Models\ManagedSolution',
            ManagedServiceToSolution::TABLE,
            'service_id',
            'solution_id')
            ->withTimestamps()
            ->using('App\Models\ManagedServiceToSolutionPivot');
    }
}
