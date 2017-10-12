<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 11.10.17
 * Time: 16:59
 */

namespace App\Keychest\Services\KeyCheck;


class SMIMEKeyResult
{
    public $fprint;
    public $subject;
    public $issuerOrg;
    public $email;
    public $modulus;
    public $bitSize;
    public $createdAt;
    public $notValidAfter;
    public $marked;
    public $status;
}
