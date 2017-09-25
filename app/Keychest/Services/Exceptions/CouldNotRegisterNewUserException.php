<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 13.09.17
 * Time: 0:13
 */

namespace App\Keychest\Services\Exceptions;


use Throwable;

class CouldNotRegisterNewUserException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}