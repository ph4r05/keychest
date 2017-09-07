<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 07.09.17
 * Time: 21:05
 */
namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class IpAddress extends Model
{
    protected $guarded = array();

    protected $table = 'ip_address';

    public function getDates()
    {
        return array();
    }
}

