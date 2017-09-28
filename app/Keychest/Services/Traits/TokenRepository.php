<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 28.09.17
 * Time: 15:07
 */

namespace App\Keychest\Services\Traits;


use App\Keychest\Utils\ParamTools;
use App\Keychest\Utils\TokenTools;
use App\Models\AccessToken;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait TokenRepository
{
    /**
     * The hashing key.
     *
     * @var string
     */
    protected $hashKey;

    /**
     * The Hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * Create a new token.
     *
     * @param $apiKey
     * @param Collection|null $options
     * @return AccessToken
     */
    public function create($apiKey, $options=null){

        $this->deleteExisting($apiKey);

        $tokenId = $this->createId();
        $token = $this->createNewToken();

        $tokenObj = new AccessToken($this->getPayload($apiKey, $tokenId, $token, $options));
        $tokenObj->save();

        $tokenObj->setTokenToSend(TokenTools::buildToken($tokenId, $token));
        return $tokenObj;
    }

    /**
     * Determine if the token has expired.
     *
     * @param  string  $expiresAt
     * @return bool
     */
    protected function tokenExpired($expiresAt)
    {
        return Carbon::parse($expiresAt)->isPast();
    }

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired()
    {
        $expiredAt = Carbon::now();
        AccessToken::where('expires_at', '<', $expiredAt)->delete();
    }

    /**
     * Delete all existing reset tokens from the database.
     *
     * @param  $obj
     * @return int
     */
    protected function deleteExisting($obj)
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * Augments query with action_type condition if set.
     * @param $options
     * @param $q
     * @return mixed
     */
    protected function actionOptions($options, $q){
        $options = ParamTools::col($options);
        if ($options->has('action')){
            $q = $q->where('action_type', '=', $options->get('action'));
        }
        return $q;
    }

    /**
     * Create a new token.
     *
     * @return string
     */
    protected function createNewToken()
    {
        return hash_hmac('sha256', Str::random(64), '');
    }

    /**
     * Token ID
     * @return string
     */
    protected function createId()
    {
        return Str::random(24);
    }

}