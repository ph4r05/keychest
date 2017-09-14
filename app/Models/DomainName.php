<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 07.09.17
 * Time: 21:05
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class DomainName extends Model
{
    const TABLE = 'domain_name';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return array();
    }

}

