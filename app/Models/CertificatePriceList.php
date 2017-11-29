<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 26.05.17
 * Time: 14:31
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CertificatePriceList extends Model
{
    const TABLE = 'certificate_price_list';

    protected $guarded = array();

    protected $table = self::TABLE;
}
