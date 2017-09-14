<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 14.09.17
 * Time: 11:43
 */

namespace App\Keychest\DataClasses;


use App\User;

class UnsubscribeResult
{
    /**
     * @var null|User
     */
    protected $user;

    /**
     * @var bool
     */
    protected $tokenFound = false;

    /**
     * UnsubscribeResult constructor.
     * @param $user
     */
    public function __construct($user=null)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTokenFound()
    {
        return $this->tokenFound;
    }

    /**
     * @param bool $tokenFound
     * @return $this
     */
    public function setTokenFound($tokenFound)
    {
        $this->tokenFound = $tokenFound;
        return $this;
    }



}