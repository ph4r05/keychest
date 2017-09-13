<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 13.09.17
 * Time: 10:47
 */

namespace App\Keychest\Utils;


use App\Keychest\Utils\DomainTools;
use App\Keychest\Utils\IpRange\InvalidRangeException;
use Exception;
use Illuminate\Support\Facades\Log;
use IPLib\Factory;
use IPLib\Range\RangeInterface;

class IpRange
{
    const RANGE_RE1 = '/^\s*([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\s*-\s*([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\s*$/';
    const RANGE_RE2 = '/^\s*([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\s*\/\s*([0-9]{1,2})\s*$/';

    /**
     * @var string
     */
    protected $ipStart;

    /**
     * @var string
     */
    protected $ipStop;

    /**
     * @var int|null
     */
    protected $rangeSize;

    /**
     * @var
     */
    protected $ipStartObj;

    /**
     * @var
     */
    protected $ipStopObj;

    /**
     * IpRange constructor.
     * @param $ipStart
     * @param $ipStop
     */
    public function __construct($ipStart=null, $ipStop=null)
    {
        $this->ipStart = $ipStart;
        $this->ipStop = $ipStop;
    }

    /**
     * @return mixed
     */
    public function getStartAddress()
    {
        return $this->ipStart;
    }

    /**
     * @param mixed $ipStart
     */
    public function setStartAddress($ipStart)
    {
        $this->ipStart = $ipStart;
        $this->rangeSize = null;
        $this->ipStartObj = null;
    }

    /**
     * @return mixed
     */
    public function getEndAddress()
    {
        return $this->ipStop;
    }

    /**
     * @param mixed $ipStop
     */
    public function setEndAddress($ipStop)
    {
        $this->ipStop = $ipStop;
        $this->rangeSize = null;
        $this->ipStopObj = null;
    }

    /**
     * Range size function
     */
    public function getSize(){
        if ($this->rangeSize === null && $this->getStartAddress() != null && $this->getEndAddress() != null){
            $this->rangeSize = self::rangeSize($this->getStartAddress(), $this->getEndAddress());
        }

        return $this->rangeSize;
    }

    /**
     * Start address repr.
     * @return \IPLib\Address\AddressInterface|null
     */
    public function getIpStartObj(){
        if (empty($this->ipStartObj) && $this->getStartAddress()){
            $this->ipStartObj = Factory::addressFromString($this->getStartAddress(), false);
        }

        return $this->ipStartObj;
    }

    /**
     * End address repr.
     * @return \IPLib\Address\AddressInterface|null
     */
    public function getIpStopObj(){
        if (empty($this->ipStopObj) && $this->getEndAddress()){
            $this->ipStopObj = Factory::addressFromString($this->getEndAddress(), false);
        }

        return $this->ipStopObj;

    }

    /**
     * Returns true if there is an intersection with given range
     * @param IpRange $range
     * @return bool
     */
    public function hasIntersection(IpRange $range){
        return !$this->isDisjoint($range);
    }

    /**
     * Returns true if given range is disjoint with the current one.
     * @param IpRange $range
     * @return bool
     */
    public function isDisjoint(IpRange $range)
    {
        return DataTools::compareArrays(
                $range->getIpStopObj()->getBytes(),
                $this->getIpStartObj()->getBytes()
            ) < 0
            ||
            DataTools::compareArrays(
                $this->getIpStopObj()->getBytes(),
                $range->getIpStartObj()->getBytes()
            ) < 0;
    }

    public function __toString()
    {
        return $this->getStartAddress() . ' - ' . $this->getEndAddress();
    }

    /**
     * Factory from the string
     * Supports two range formats.
     * @param $str
     * @return IpRange
     * @throws InvalidRangeException
     */
    public static function rangeFromString($str)
    {
        $m1 = null;
        $m2 = null;

        $r1 = preg_match(self::RANGE_RE1, $str, $m1);
        $r2 = preg_match(self::RANGE_RE2, $str, $m2);
        if (!$r1 && !$r2) {
            throw new InvalidRangeException('Unrecognized range format');
        }

        try {
            if ($r1) {
                $ipBeg = Factory::addressFromString($m1[1]);
                $ipEnd = Factory::addressFromString($m1[2]);
                if ($ipBeg->getComparableString() > $ipEnd->getComparableString()){
                    throw new InvalidRangeException('Start is greater than end');
                }

                return new IpRange($m1[1], $m1[2]);
            }

            $range = \IPLib\Factory::rangeFromString($str);
            return new IpRange($range->getStartAddress(), $range->getEndAddress());

        } catch (Exception $e) {
            throw new InvalidRangeException('Invalid range', 0, $e);
        }
    }

    /**
     * Computes the range size
     * @param $ipBeg
     * @param $ipEnd
     * @return int
     */
    public static function rangeSize($ipBeg, $ipEnd)
    {
        $ipBeg = Factory::addressFromString($ipBeg, false)->getBytes();
        $ipEnd = Factory::addressFromString($ipEnd, false)->getBytes();
        $size = 0;

        for ($i = 0; $i < 4; $i++) {
            if ($ipBeg[$i] === $ipEnd[$i]) {
                continue;
            }

            $size += ($ipBeg[$i] - $ipEnd[$i]) * (2 ** ((3 - $i) * 8));
        }
        return $size + 1;
    }
}