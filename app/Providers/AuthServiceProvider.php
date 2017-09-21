<?php

namespace App\Providers;


use App\Http\Guards\ApiKeyGuard;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Extended user provider - working with api key
        Auth::provider('ph4-eloquent', function($app, array $config){
            return new UserServiceProvider($this->app['hash'], $config['model']);
        });

        // New guard for api keys
        Auth::extend('ph4-token', function($app, $name, array $config){
            Log::info('creating guard');
            $guard = new ApiKeyGuard(
                $this->app['auth']->createUserProvider($config['provider']),
                $this->app['request']
            );

            $this->app->refresh('request', $guard, 'setRequest');
            return $guard;
        });
    }
}
