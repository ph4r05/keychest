<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 11.10.17
 * Time: 16:59
 */

namespace App\Keychest\Services\KeyCheck;


class PGPKeyResult
{
    public $keyId;
    public $masterKeyId;
    public $modulus;
    public $bitSize;
    public $identity;
    public $createdAt;
    public $marked;
    public $status;
    public $verdict;
}
