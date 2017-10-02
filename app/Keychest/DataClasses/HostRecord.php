<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 02.10.17
 * Time: 20:46
 */

namespace App\Keychest\DataClasses;


use App\Models\WatchTarget;

class HostRecord
{
    /**
     * @var WatchTarget
     */
    protected $host;

    /**
     * Validity model for the host
     * @var
     */
    protected $validityModel;

    /**
     * @return WatchTarget
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param WatchTarget $host
     * @return HostRecord
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValidityModel()
    {
        return $this->validityModel;
    }

    /**
     * @param mixed $validityModel
     * @return HostRecord
     */
    public function setValidityModel($validityModel)
    {
        $this->validityModel = $validityModel;
        return $this;
    }



}