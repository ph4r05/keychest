<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 16.11.17
 * Time: 14:42
 */

namespace App\Keychest\Services\Management;


class HostDbSpec
{
    protected $name;
    protected $address;
    protected $port;
    protected $agent;

    /**
     * HostDbSpec constructor.
     * @param $name
     * @param $address
     * @param $port
     * @param $agent
     */
    public function __construct($name=null, $address=null, $port=null, $agent=null)
    {
        $this->name = $name;
        $this->address = $address;
        $this->port = $port;
        $this->agent = $agent;
    }


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return HostDbSpec
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     * @return HostDbSpec
     */
    public function setAddress($address)
    {
        $this->address = $address;
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
     * @return HostDbSpec
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param mixed $agent
     * @return HostDbSpec
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
        return $this;
    }


}