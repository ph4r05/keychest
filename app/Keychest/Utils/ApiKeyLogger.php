<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 27.09.17
 * Time: 12:13
 */

namespace App\Keychest\Utils;


use App\Models\ApiKey;
use App\Models\ApiKeyLog;
use App\Models\User;
use Carbon\Carbon;

class ApiKeyLogger
{
    /**
     * @var
     */
    protected $now;

    /**
     * @var
     */
    protected $ip;

    /**
     * @var
     */
    protected $email;

    /**
     * @var
     */
    protected $apiId;

    /**
     * @var
     */
    protected $challenge;

    /**
     * @var
     */
    protected $action;

    /**
     * @var
     */
    protected $actionData;

    public function __construct()
    {

    }

    /**
     * Factory
     * @return ApiKeyLogger
     */
    public static function create(){
        return new ApiKeyLogger();
    }

    /**
     * Stores the model to the database
     */
    public function save(){
        $now = $this->now ? $this->now : Carbon::now();
        $log = new ApiKeyLog([
            'created_at' => $now,
            'updated_at' => $now,
            'req_ip' => $this->ip,
            'req_email' => $this->email,
            'req_challenge' => $this->challenge,
            'api_key_id' => $this->apiId,
            'action_type' => $this->action,
            'action_data' => $this->actionData,
        ]);
        $log->save();
        return $log;
    }

    /**
     * Use request to fill in data
     * @param mixed $request
     * @return ApiKeyLogger
     */
    public function request($request)
    {
        $this->ip = $request ? $request->ip() : null;
        return $this;
    }

    /**
     * Use User object to fill in data
     * @param User $user
     * @return $this
     */
    public function user(User $user)
    {
        $this->email = $user ? $user->email : null;
        return $this;
    }

    /**
     * Use ApiKey object to fill in data
     * @param ApiKey $apiKey
     * @return $this
     */
    public function apiKey(ApiKey $apiKey)
    {
        $this->apiId = $apiKey ? $apiKey->id : null;
        $this->challenge = $apiKey ? $apiKey->api_key : null;
        return $this;
    }

    // ----------------------------------------------------------------------------------------------------------------
    //  Getters & Setters

    /**
     * @return mixed
     */
    public function getNow()
    {
        return $this->now;
    }

    /**
     * @param mixed $now
     * @return ApiKeyLogger
     */
    public function now($now)
    {
        $this->now = $now;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     * @return ApiKeyLogger
     */
    public function ip($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return ApiKeyLogger
     */
    public function email($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChallenge()
    {
        return $this->challenge;
    }

    /**
     * @param mixed $challenge
     * @return ApiKeyLogger
     */
    public function challenge($challenge)
    {
        $this->challenge = $challenge;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     * @return ApiKeyLogger
     */
    public function action($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getActionData()
    {
        return $this->actionData;
    }

    /**
     * @param mixed $actionData
     * @return ApiKeyLogger
     */
    public function actionData($actionData)
    {
        $this->actionData = $actionData;
        return $this;
    }

}
