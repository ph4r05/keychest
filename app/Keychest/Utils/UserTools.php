<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 17:03
 */

namespace App\Keychest\Utils;

use App\User;
use Illuminate\Support\Collection;
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
    public static function generateVerifyToken($user){
        return (new Factory())->getLowStrengthGenerator()->generateString(24, self::TOKEN_CHARSET);
    }

    /**
     * Unsubscribe token
     * @param $user
     * @return string
     */
    public static function generateUnsubscribeToken($user){
        return (new Factory())->getLowStrengthGenerator()->generateString(24, self::TOKEN_CHARSET);
    }
}
