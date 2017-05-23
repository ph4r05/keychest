<?php

namespace App\Providers;

use App\Keychest\Queue\Ph4RedisBroadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Foundation\Application;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @param BroadcastManager $broadcastManager
     * @return void
     */
    public function boot(BroadcastManager $broadcastManager)
    {
        $broadcastManager->extend('ph4redis', function (Application $app, array $config) {
            return new Ph4RedisBroadcaster(
                $app->make('redis'), Arr::get($config, 'connection')
            );
        });

        Broadcast::routes();

        require base_path('routes/channels.php');
    }
}
