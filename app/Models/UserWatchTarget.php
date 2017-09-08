<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 07.09.17
 * Time: 20:25
 */
namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserWatchTarget extends Pivot
{
    protected $guarded = array();

    protected $table = 'user_watch_target';

    protected $foreignKey = 'user_id';

    protected $relatedKey = 'watch_id';

    public function getDates()
    {
        return array('created_at', 'updated_at', 'deleted_at', 'disabled_at');
    }
}

