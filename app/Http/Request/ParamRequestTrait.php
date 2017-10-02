<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 02.10.17
 * Time: 14:12
 */

namespace App\Http\Request;


use Illuminate\Support\Collection;

trait ParamRequestTrait
{
    /**
     * @var Collection
     */
    protected $extraParams = null;

    /**
     * Augments response with extra parameters passed.
     * @param $arr
     * @return mixed
     */
    protected function augmentAllResponse($arr)
    {
        if (!empty($this->extraParams))
        {
            foreach($this->extraParams as $key => $val)
            {
                $arr[$key] = $val;
            }
        }
        return $arr;
    }

    /**
     * Get access to the collection of extra parameters.
     * @return Collection
     */
    public function getExtraParams()
    {
        if (!$this->extraParams)
        {
            $this->extraParams = collect();
        }

        return $this->extraParams;
    }

    /**
     * Adds extra param to the all()
     * @param $key
     * @param $val
     * @return $this
     */
    public function addExtraParam($key, $val)
    {
        $this->getExtraParams()->put($key, $val);
        return $this;
    }

    /**
     * Array of extra params to add to all()
     * @param $params
     * @return $this
     */
    public function addEtraParams($params)
    {
        if (empty($params)){
            return $this;
        }

        foreach ($params as $key => $val)
        {
            $this->addExtraParam($key, $val);
        }

        return $this;
    }

    /**
     * @param Collection $extraParams
     */
    public function setExtraParams($extraParams)
    {
        $this->extraParams = $extraParams;
    }

    /**
     * Resets cached acceptable content type
     * forcing next getter to recompute it from getter.
     */
    public function clearCachedAcceptTypes()
    {
        $this->acceptableContentTypes = null;
    }
}
