<?php

namespace App\Providers;


use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\ScanJobProgress' => [
            'App\Listeners\ScanJobListener',
        ],

        'App\Events\TesterJobProgress' => [
            'App\Listeners\TesterJobListener',
        ],

        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\OnUserLogin',
        ],

        'Illuminate\Auth\Events\Registered' => [
            'App\Listeners\OnUserRegistered',
        ],

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
