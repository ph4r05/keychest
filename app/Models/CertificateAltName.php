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

class CertificateAltName extends Model
{
    public $incrementing = true;

    protected $guarded = array();

    protected $table = 'certificate_alt_names';
}
