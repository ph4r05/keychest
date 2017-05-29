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

class Certificate extends Model
{
    public $incrementing = false;

    protected $guarded = array();

    protected $table = 'certificates';

    public function getDates()
    {
        return array('created_at', 'updated_at', 'valid_from', 'valid_to');
    }
}
