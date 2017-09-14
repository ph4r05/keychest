<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 26.05.17
 * Time: 14:31
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CertificateAltName extends Model
{
    const TABLE = 'certificate_alt_names';

    public $incrementing = true;

    protected $guarded = array();

    protected $table = self::TABLE;
}
