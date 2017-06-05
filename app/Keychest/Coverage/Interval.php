<?php

namespace App\Keychest\Coverage;

/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 04.06.17
 * Time: 15:05
 */
class Interval {
    protected $start;
    protected $end;

    /**
     * Interval constructor.
     * @param $start
     * @param $end
     */
    public function __construct($start, $end)
    {
        $this->start = $start <= $end ? $start : $end;
        $this->end = $start <= $end ? $end : $start;
    }

    /**
     * Size of the interval
     * @return mixed
     */
    public function size(){
        return $this->end - $this->start;
    }

    /**
     * Returns true if given point is contained in the interval
     * @param $point
     * @return bool
     */
    public function contains($point){
        return $point >= $this->start && $point <= $this->end;
    }

    /**
     * True if intervals overlap somehow
     * @param Interval $interval
     * @return bool
     */
    public function hasOverlap($interval){
        return !(
            ($this->start <= $interval->start && $this->end <= $interval->start) ||
            ($this->start >= $interval->end   && $this->end >= $interval->end)
        ); // with negation it is easier.
    }

    /**
     * Gap size for non-overlaping intervals, 0 otherwise
     * @param Interval $interval
     * @return int
     */
    public function hasGap($interval){
        if ($this->hasOverlap($interval)){
            return 0;
        }

        if ($interval->getEnd() <= $this->getStart()){
            return $this->getStart() - $interval->getEnd();
        } else {
            return $interval->getStart() - $this->getEnd();
        }
    }

    /**
     * Returns gap between two intervals, null if there is an overlap
     * @param Interval $interval
     * @return Interval|null
     */
    public function gap($interval){
        if ($this->hasOverlap($interval)){
            return null;
        }

        if ($interval->getEnd() <= $this->getStart()){
            return new Interval($interval->getEnd(), $this->getStart());
        } else {
            return new Interval($this->getEnd(), $interval->getStart());
        }
    }

    /**
     * Absorbs interval to the current one so the resulting interval covers both.
     *
     * @param Interval $interval
     * @param $alsoNonOverlaping
     * @return Interval|null gap interval when absorbing non-overlaping intervals, or null on overlap
     * @throws \Exception when absorbing non-overlaping intervals
     */
    public function absorb($interval, $alsoNonOverlaping=true){
        if (!$alsoNonOverlaping && $this->hasOverlap($interval)){
            throw new \Exception("Absorbing non-overlaping intervals");
        }

        $gp = $this->gap($interval);
        $this->start = min($this->start, $interval->getStart());
        $this->end = max($this->end, $interval->getEnd());
        return $gp;
    }

    /**
     * @return mixed
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return mixed
     */
    public function getEnd()
    {
        return $this->end;
    }

}

