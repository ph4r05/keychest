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
        throw new \Exception('Not implemented');
    }

    public function setAttribute($key, $value)
    {
        throw new \Exception('Not implemented');
    }
}
