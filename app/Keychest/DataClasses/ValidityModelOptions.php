<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 02.10.17
 * Time: 21:40
 */

namespace App\Keychest\DataClasses;


class ValidityModelOptions
{
    /**
     * @var bool
     */
    public $expandModel = true;

    /**
     * @var int
     */
    public $crtshCertLimit = 1000;

    /**
     * @var bool
     */
    public $loadTls = true;

    /**
     * @var bool
     */
    public $loadCrtsh = true;

    /**
     * @return bool
     */
    public function shouldExpandModel()
    {
        return $this->expandModel;
    }

    /**
     * @param bool $expandModel
     * @return ValidityModelOptions
     */
    public function setExpandModel($expandModel)
    {
        $this->expandModel = $expandModel;
        return $this;
    }

    /**
     * @return int
     */
    public function getCrtshCertLimit()
    {
        return $this->crtshCertLimit;
    }

    /**
     * @param int $crtshCertLimit
     * @return ValidityModelOptions
     */
    public function setCrtshCertLimit($crtshCertLimit)
    {
        $this->crtshCertLimit = $crtshCertLimit;
        return $this;
    }

    /**
     * @return bool
     */
    public function shouldLoadTls()
    {
        return $this->loadTls;
    }

    /**
     * @param bool $loadTls
     * @return ValidityModelOptions
     */
    public function setLoadTls($loadTls)
    {
        $this->loadTls = $loadTls;
        return $this;
    }

    /**
     * @return bool
     */
    public function shouldLoadCrtsh()
    {
        return $this->loadCrtsh;
    }

    /**
     * @param bool $loadCrtsh
     * @return ValidityModelOptions
     */
    public function setLoadCrtsh($loadCrtsh)
    {
        $this->loadCrtsh = $loadCrtsh;
        return $this;
    }


}