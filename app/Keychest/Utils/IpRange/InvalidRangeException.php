<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 12.09.17
 * Time: 23:01
 */

namespace App\Keychest\Utils\IpRange;


use Throwable;

class InvalidRangeException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
