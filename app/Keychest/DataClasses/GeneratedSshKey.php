<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 26.10.17
 * Time: 16:02
 */

namespace App\Keychest\DataClasses;


class GeneratedSshKey
{
    public $privateKey;
    public $publicKey;
    public $bitSize;

    /**
     * GeneratedSshKey constructor.
     * @param $privateKey
     * @param $publicKey
     * @param int $bitSize
     */
    public function __construct($privateKey, $publicKey, $bitSize=0)
    {
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
        $this->bitSize = $bitSize;
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @return mixed
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @return mixed
     */
    public function getBitSize()
    {
        return $this->bitSize;
    }

    /**
     * @param mixed $privateKey
     * @return GeneratedSshKey
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
        return $this;
    }

    /**
     * @param mixed $publicKey
     * @return GeneratedSshKey
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
        return $this;
    }

    /**
     * @param mixed $bitSize
     * @return GeneratedSshKey
     */
    public function setBitSize($bitSize)
    {
        $this->bitSize = $bitSize;
        return $this;
    }

}
