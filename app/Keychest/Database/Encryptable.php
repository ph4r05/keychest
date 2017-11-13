<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 13.11.17
 * Time: 15:15
 */

namespace App\Keychest\Database;

use Illuminate\Support\Facades\Crypt;

/**
 * Encryptable Eloquent attributes with app password and standard Crypt facade.
 *
 * Usage:
 *
 * <code>
 * use Encryptable;
 *
 * protected $encryptable = [
 *      'attr1',
 *      'attr2
 * ];
 *
 * </code>
 *
 * Trait Encryptable
 */
trait Encryptable
{
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable)) {
            $value = Crypt::decrypt($value);
        }

        return $value;
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable)) {
            $value = Crypt::encrypt($value);
        }

        return parent::setAttribute($key, $value);
    }
}
