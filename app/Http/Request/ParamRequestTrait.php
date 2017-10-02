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
    protected $extraParams;

    /**
     * @return Collection
     */
    public function getExtraParams()
    {
        return $this->extraParams;
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