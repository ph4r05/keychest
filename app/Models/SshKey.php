<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 25.10.17
 * Time: 14:39
 */

namespace App\Models;

use App\Keychest\Database\Encryptable;
use Illuminate\Database\Eloquent\Model;


class SshKey extends Model
{
    use Encryptable;

    const TABLE = 'ssh_keys';

    protected $table = self::TABLE;

    protected $guarded = [];

    protected $hidden = ['priv_key'];

    protected $encryptable = ['priv_key'];

    public function getDates()
    {
        return array('created_at', 'updated_at', 'revoked_at');
    }

    /**
     * Get the user that owns the phone.
     */
    public function owner()
    {
        return $this->belongsTo('App\Models\Owner', 'owner_id');
    }
}
