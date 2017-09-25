<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 20.09.17
 * Time: 22:04
 */

namespace App\Providers;


use App\Models\ApiKey;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserServiceProvider extends EloquentUserProvider
{
    const API_KEY = 'api_key';

    /**
     * UserServiceProvider constructor.
     * @param HasherContract $hasher
     * @param string $model
     */
    public function __construct(HasherContract $hasher, $model)
    {
        parent::__construct($hasher, $model);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials, may contain api_key, then api key table is used for auth
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials)) {
            return;
        }

        // Api key auth - user has multiple
        if (isset($credentials[self::API_KEY])){
            return $this->retrieveByApiKey($credentials);
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->createModel()->newQuery();

        foreach ($credentials as $key => $value) {
            if (! Str::contains($key, 'password') && $key !== self::API_KEY) {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }

    /**
     * Validate API key only
     * @param array $credentials
     * @return null
     */
    public function retrieveByApiKey(array $credentials)
    {
        $token = ApiKey::query()
            ->where('api_key', '=', $credentials['api_key'])
            ->whereNull('revoked_at')
            ->first();

        $u = $token ? $token->user()->first() : null;
        if ($u){
            $u->apiKey = $token;
        }

        return $u;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        if (isset($credentials[self::API_KEY])){
            return $this->validateApiKey($user, $credentials);
        }

        $plain = $credentials['password'];
        return $this->hasher->check($plain, $user->getAuthPassword());
    }

    /**
     * @param UserContract $user
     * @param array $credentials
     * @return bool
     */
    public function validateApiKey(UserContract $user, array $credentials)
    {
        return $user
            && $user->apiKey
            && $credentials[self::API_KEY]
            && $user->apiKey == $credentials[self::API_KEY];
    }

}
