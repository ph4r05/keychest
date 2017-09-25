<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 21.09.17
 * Time: 21:42
 */

namespace App\Keychest\Services\Results;


use App\Keychest\Utils\Enum;

class ApiKeySelfRegistrationResult extends Enum
{
    static public $ALLOWED = 0;

    static public $GLOBAL_DENIAL = 1;
    static public $BLACKLISTED = 2;
    static public $RATE_LIMITED = 3;
    static public $TOO_MANY = 4;
}
