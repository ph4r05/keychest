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

    /**
     * GeneratedSshKey constructor.
     * @param $privateKey
     * @param $publicKey
     */
    public function __construct($privateKey, $publicKey)
    {
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param mixed $privateKey
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
    }

    /**
     * @return mixed
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param mixed $publicKey
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
    }




}
