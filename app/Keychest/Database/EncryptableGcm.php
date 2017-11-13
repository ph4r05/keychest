<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 13.11.17
 * Time: 15:16
 */

namespace App\Keychest\Database;

use Illuminate\Support\Facades\Crypt;

/**
 * Encryptable Eloquent attributes with PBKDF2 + AES-GCM scheme.
 *
 * TODO: implement PBKDF2 salting with custom Encrypter: @see \Illuminate\Encryption\Encrypter
 *
 * Trait EncryptableGcm
 */
trait EncryptableGcm
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
