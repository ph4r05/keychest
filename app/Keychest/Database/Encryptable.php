<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 13.11.17
 * Time: 15:15
 */

namespace App\Keychest\Database;

use App\Keychest\Services\Exceptions\DecryptionFailException;
use Illuminate\Support\Facades\Crypt;
use stdClass;

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
            $value = $this->decryptAttributeValue($key, $value);
        }

        return $value;
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable)) {
            $value = $this->encryptAttributeValue($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Returns base encryptor scheme.
     * Forward compatibility, enables to change encryptor schemes / support more.
     * @return string
     */
    public static function getBaseEncryptScheme(){
        return 'base';
    }

    /**
     * Encrypts given attribute value
     * @param $key
     * @param $value
     * @return null
     */
    protected function encryptAttributeValue($key, $value){
        $wrapper = new StdClass();
        $wrapper->scheme = self::getBaseEncryptScheme();
        $wrapper->val = Crypt::encrypt($value);
        return json_encode($wrapper);
    }

    /**
     * Decrypts value of the attribute
     * @param $key
     * @param $value
     * @return null
     * @throws DecryptionFailException
     */
    protected function decryptAttributeValue($key, $value){
        if (empty($value)){
            return null;
        }

        $wrapper = json_decode($value);
        if (empty($wrapper)){
            return null;
        }

        if (!isset($wrapper->scheme) || $wrapper->scheme != self::getBaseEncryptScheme()){
            throw new DecryptionFailException('Unknown / unrecognized scheme');
        }

        return Crypt::decrypt($wrapper->val);
    }
}
