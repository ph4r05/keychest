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

class DomainName extends Model
{
    protected $guarded = array();

    protected $table = 'domain_name';

    public function getDates()
    {
        return array();
    }

}

