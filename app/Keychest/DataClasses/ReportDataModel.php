<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 17.08.17
 * Time: 11:33
 */
namespace App\Keychest\DataClasses;

use App\User;


use Illuminate\Support\Collection;

class ReportDataModel
{
    // use SerializesModels;

    /**
     * @var User
     */
    public $user;

    /**
     * @var int
     */
    public $numActiveWatches;

    /**
     * @var int
     */
    public $numAllCerts;

    /**
     * @var int
     */
    public $numCertsActive;

    /**
     * @var Collection
     */
    public $certExpired;

    /**
     * @var Collection
     */
    public $certExpire7days;

    /**
     * @var Collection
     */
    public $certExpire28days;

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    |
    */

    /**
     * ValidityDataModel constructor.
     * @param User|null $user
     */
    public function __construct(User $user=null)
    {
        $this->setUser($user);
    }

    /*
    |--------------------------------------------------------------------------
    | Getters & Setters
    |--------------------------------------------------------------------------
    |
    */

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return int
     */
    public function getNumActiveWatches()
    {
        return $this->numActiveWatches;
    }

    /**
     * @param int $numActiveWatches
     */
    public function setNumActiveWatches($numActiveWatches)
    {
        $this->numActiveWatches = $numActiveWatches;
    }

    /**
     * @return int
     */
    public function getNumAllCerts()
    {
        return $this->numAllCerts;
    }

    /**
     * @param int $numAllCerts
     */
    public function setNumAllCerts($numAllCerts)
    {
        $this->numAllCerts = $numAllCerts;
    }

    /**
     * @return int
     */
    public function getNumCertsActive()
    {
        return $this->numCertsActive;
    }

    /**
     * @param int $numCertsActive
     */
    public function setNumCertsActive($numCertsActive)
    {
        $this->numCertsActive = $numCertsActive;
    }

    /**
     * @return Collection
     */
    public function getCertExpired()
    {
        return $this->certExpired;
    }

    /**
     * @param Collection $certExpired
     */
    public function setCertExpired($certExpired)
    {
        $this->certExpired = $certExpired;
    }

    /**
     * @return Collection
     */
    public function getCertExpire7days()
    {
        return $this->certExpire7days;
    }

    /**
     * @param Collection $certExpire7days
     */
    public function setCertExpire7days($certExpire7days)
    {
        $this->certExpire7days = $certExpire7days;
    }

    /**
     * @return Collection
     */
    public function getCertExpire28days()
    {
        return $this->certExpire28days;
    }

    /**
     * @param Collection $certExpire28days
     */
    public function setCertExpire28days($certExpire28days)
    {
        $this->certExpire28days = $certExpire28days;
    }
}