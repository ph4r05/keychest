<?php

namespace App\Rules;

use App\Keychest\Utils\DomainTools;
use App\Keychest\Utils\HostSpec;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class HostSpecObjRule implements Rule
{
    protected $invalidObject = false;
    protected $invalid = false;
    protected $invalidIpv4 = false;
    protected $invalidIpv6 = false;
    protected $invalidHost = false;
    protected $invalidPort = false;

    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($value) || !($value instanceof HostSpec)){
            $this->invalidObject = true;
            return false;
        }

        $addrType = $value->getAddrType();
        if ($addrType == HostSpec::ADDR_DOMAIN){
            //
        } elseif ($addrType == HostSpec::ADDR_IPv4){
            $this->invalidIpv4 = !DomainTools::isIpv4Valid($value->getAddr());
        } elseif ($addrType == HostSpec::ADDR_IPv6){
            $this->invalidIpv6 = !DomainTools::isIpv6Valid($value->getAddr());
        } else {
            $this->invalid = true;
        }

        if (!empty($value->getPort()) && !DomainTools::isPortValid($value->getPort())){
            $this->invalidPort = true;
        }

        return !$this->invalid
            && !$this->invalidIpv6
            && !$this->invalidHost
            && !$this->invalidPort
            && !$this->invalidObject;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->invalid){
            return 'The :attribute does not define host address correctly';
        }

        if ($this->invalidIpv4){
            return 'The :attribute contains invalid IPv4 address';
        }

        if ($this->invalidIpv6){
            return 'The :attribute contains invalid IPv6 address';
        }

        if ($this->invalidPort){
            return 'The :attribute contains invalid port';
        }

        return 'The :attribute is invalid';
    }
}
