<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 07.09.17
 * Time: 21:05
 */
namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class IpAddress extends Model
{
    const TABLE = 'ip_address';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return array();
    }
}

