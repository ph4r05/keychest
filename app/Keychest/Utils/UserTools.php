<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 17:03
 */

namespace App\Keychest\Utils;

use App\Models\User;

use RandomLib\Factory;

/**
 * User tools
 *
 * Class UserTools
 * @package App\Keychest\Utils
 */
class UserTools {

    const TOKEN_CHARSET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * Generates accredit value
     * @param User $user
     * @return string
     */
    public static function accredit($user){
        return empty($user) ? null : substr(md5($user->id . ':' . $user->email), 0, 24);
    }

    /**
     * Email verification token
     * @param $user
     * @return string
     */
    public static function generateVerifyToken($user=null){
        return (new Factory())->getLowStrengthGenerator()->generateString(24, self::TOKEN_CHARSET);
    }

    /**
     * Unsubscribe token
     * @param $user
     * @return string
     */
    public static function generateUnsubscribeToken($user=null){
        return (new Factory())->getLowStrengthGenerator()->generateString(24, self::TOKEN_CHARSET);
    }

    /**
     * Returns true if the user was properly registered by the register event.
     * e.g., primary owner is filled in.
     * @param User $user
     * @return bool
     */
    public static function wasUserProperlyRegistered($user){
        return !empty($user->primary_owner_id);
    }
}
