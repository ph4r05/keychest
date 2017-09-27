<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 27.09.17
 * Time: 10:28
 */

namespace App\Keychest\Services\Exceptions;

use Throwable;

class CannotSendEmailException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}