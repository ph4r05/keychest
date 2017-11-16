<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 16.11.17
 * Time: 15:24
 */

namespace App\Keychest\Utils;


use Illuminate\Support\Collection;


use IPLib\Address\AddressInterface;
use IPLib\Factory;

use TrueBV\Punycode;

/**
 * Result of hostspec parse
 *
 * Class HostSpec
 * @package App\Keychest\Utils
 */
class HostSpec {
    const ADDR_IPv4 = 1;
    const ADDR_IPv6 = 2;
    const ADDR_DOMAIN = 3;

    public $addr;
    public $port;
    public $addrType;

    /**
     * HostSpec constructor.
     * @param $addr
     * @param $port
     * @param $addrType
     */
    public function __construct($addr, $port, $addrType)
    {
        $this->addr = $addr;
        $this->port = $port;
        $this->addrType = $addrType;
    }

    /**
     * @return mixed
     */
    public function getAddr()
    {
        return $this->addr;
    }

    /**
     * @param mixed $addr
     * @return HostSpec
     */
    public function setAddr($addr)
    {
        $this->addr = $addr;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     * @return HostSpec
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddrType()
    {
        return $this->addrType;
    }

    /**
     * @param mixed $addrType
     * @return HostSpec
     */
    public function setAddrType($addrType)
    {
        $this->addrType = $addrType;
        return $this;
    }


}

