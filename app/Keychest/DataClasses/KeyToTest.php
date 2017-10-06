<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 06.10.17
 * Time: 22:33
 */

namespace App\Keychest\DataClasses;


class KeyToTest
{
    /**
     * @var
     */
    public $id;

    /**
     * @var
     */
    public $keyValue;

    /**
     * @var
     */
    public $keyName;

    /**
     * @var
     */
    public $keyType;

    /**
     * @var
     */
    public $pgp;

    /**
     * KeyToTest constructor.
     * @param $uuid
     */
    public function __construct($uuid = null)
    {
        $this->id = $uuid;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return KeyToTest
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKeyValue()
    {
        return $this->keyValue;
    }

    /**
     * @param mixed $keyValue
     * @return KeyToTest
     */
    public function setKeyValue($keyValue)
    {
        $this->keyValue = $keyValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * @param mixed $keyName
     * @return KeyToTest
     */
    public function setKeyName($keyName)
    {
        $this->keyName = $keyName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKeyType()
    {
        return $this->keyType;
    }

    /**
     * @param mixed $keyType
     * @return KeyToTest
     */
    public function setKeyType($keyType)
    {
        $this->keyType = $keyType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPgp()
    {
        return $this->pgp;
    }

    /**
     * @param mixed $pgp
     * @return KeyToTest
     */
    public function setPgp($pgp)
    {
        $this->pgp = $pgp;
        return $this;
    }

}