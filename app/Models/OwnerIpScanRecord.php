<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 07.09.17
 * Time: 20:25
 */
namespace App\Models;



use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class OwnerIpScanRecord extends Pivot
{
    use SoftDeletes;

    const TABLE = 'owner_ip_scan_record';

    protected $guarded = array();

    protected $table = self::TABLE;

    protected $foreignKey = 'ip_scan_record_id';

    protected $relatedKey = 'owner_id';

    public function getDates()
    {
        return array('created_at', 'updated_at', 'deleted_at', 'disabled_at');
    }
}

