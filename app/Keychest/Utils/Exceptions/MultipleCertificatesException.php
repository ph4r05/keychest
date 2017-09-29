<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 29.09.17
 * Time: 13:11
 */

namespace App\Keychest\Utils\Exceptions;


use Throwable;

class MultipleCertificatesException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
