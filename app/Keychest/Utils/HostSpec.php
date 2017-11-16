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


}

