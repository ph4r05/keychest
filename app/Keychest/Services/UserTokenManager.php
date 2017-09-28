<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;

use App\Keychest\Services\Traits\TokenRepository;
use App\Keychest\Utils\AppTools;
use App\Keychest\Utils\ParamTools;
use App\Keychest\Utils\TokenTools;
use App\Models\AccessToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;


class UserTokenManager {

    use TokenRepository;

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Create a new manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->hasher = $this->app['hash'];
        $this->hashKey = AppTools::getKey($app);
    }

    /**
     * Create a new token.
     *
     * @param User $user
     * @param Collection|null $options
     * @return AccessToken
     */
    public function create($user, $options=null){

        $options = ParamTools::col($options);
        if (!$options->get('multiple')) {
            $this->deleteExisting($user, $options);
        }

        $tokenId = $this->createId();
        $token = $this->createNewToken();

        $tokenObj = new AccessToken($this->getPayload($user, $tokenId, $token, $options));
        $tokenObj->save();

        $tokenObj->setTokenToSend(TokenTools::buildToken($tokenId, $token));
        return $tokenObj;
    }

    /**
     * Loads the token if valid.
     *
     * @param  string $token
     * @param null $options
     * @return AccessToken
     */
    public function get($token, $options=null){
        $options = ParamTools::col($options);
        list($tokenId, $tokenVal) = TokenTools::parts($token);

        $q = AccessToken::query()
            ->whereNotNull('user_id')
            ->where('token_id', '=', $tokenId);

        $q = $this->actionOptions($options, $q);
        $record = $q->with('user')->first();

        return $record &&
            ! $this->tokenExpired($record->expires_at) &&
              $this->hasher->check($tokenVal, $record->token) ? $record : null;
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  string $token
     * @param null $options
     * @return bool
     */
    public function exists($token, $options=null){
        return $this->get($token, $options) !== null;
    }

    /**
     * Delete a token record.
     *
     * @param User $user
     * @param null $options
     * @return
     */
    public function delete(User $user, $options=null){
        $q = AccessToken::where('user_id', $user->id);
        $q = $this->actionOptions($options, $q);
        return $q->delete();
    }

    /**
     * Build the record payload for the table.
     *
     * @param $user
     * @param $tokenId
     * @param string $token
     * @param $options
     * @return array
     */
    protected function getPayload($user, $tokenId, $token, $options)
    {
        $options = ParamTools::col($options);
        return [
            'user_id' => $user->id,
            'token_id' => $tokenId,
            'token' => $this->hasher->make($token),
            'created_at' => new Carbon,
            'expires_at' => Carbon::now()->addDays(7),
            'action_type' => $options->get('action', null)
        ];
    }

    /**
     * Delete all existing reset tokens from the database.
     *
     * @param  User $user
     * @param null $options
     * @return int
     */
    protected function deleteExisting(User $user, $options=null)
    {
        $q = AccessToken::where('user_id', $user->id);
        $q = $this->actionOptions($options, $q);
        return $q->delete();
    }

}
