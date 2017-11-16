<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 16.11.17
 * Time: 14:42
 */

namespace App\Keychest\Services\Management;


class HostSpec
{
    protected $name;
    protected $address;
    protected $port;
    protected $agent;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return HostSpec
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
     * @return HostSpec
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
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param mixed $agent
     * @return HostSpec
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
        return $this;
    }


}