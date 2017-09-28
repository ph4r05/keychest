<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 28.09.17
 * Time: 14:02
 */

namespace App\Keychest\Utils;


use App\Models\AccessToken;
use \InvalidArgumentException;
use Illuminate\Support\Collection;

class TokenTools
{
    /**
     * Returns the parts of the token
     * id.token
     * @param $token
     * @return Collection
     */
    public static function parts($token){
        $ret = explode('.', $token, 2);
        if (count($ret) !== 2) {
            throw new InvalidArgumentException();
        }
        return $ret;
    }

    /**
     * Returns final representation of the token
     * @param $id
     * @param $token
     * @return string
     */
    public static function buildToken($id, $token){
        return $id . '.' . $token;
    }

    /**
     * Mark token as used / disable form further usage / delete it
     * @param AccessToken $token
     * @return bool|null
     */
    public static function tokenUsed(AccessToken $token){
        return $token->delete();
    }

}