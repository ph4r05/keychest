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

/**
 * User tools
 *
 * Class UserTools
 * @package App\Keychest\Utils
 */
class UserTools {

    /**
     * Generates accredit value
     * @param User $user
     * @return string
     */
    public static function accredit($user){
        return empty($user) ? null : substr(md5($user->id . ':' . $user->email), 0, 24);
    }
}
