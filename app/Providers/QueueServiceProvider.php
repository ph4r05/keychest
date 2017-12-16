<?php

namespace App\Providers;

use App\Keychest\Queue\Ph4DatabaseConnector;
use App\Keychest\Queue\Ph4RedisConnector;
use App\Keychest\Queue\Ph4Worker;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Queue\Worker;
use Illuminate\Support\ServiceProvider;


class QueueServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $manager = $this->app['queue'];
        $this->registerConnectors($manager);
        $this->registerWorker();
    }

    /**
     * Register the connectors on the queue manager.
     *
     * @param  \Illuminate\Queue\QueueManager  $manager
     * @return void
     */
    public function registerConnectors($manager)
    {
        $this->registerPh4RedisConnector($manager);
        $this->registerPh4DatabaseConnector($manager);
    }

    /**
     * Register the Redis queue connector.
     *
     * @param  \Illuminate\Queue\QueueManager  $manager
     * @return void
     */
    protected function registerPh4RedisConnector($manager)
    {
        $manager->addConnector('ph4redis', function () {
            return new Ph4RedisConnector($this->app['redis']);
        });
    }

    /**
     * Register the database queue connector.
     *
     * @param  \Illuminate\Queue\QueueManager  $manager
     * @return void
     */
    protected function registerPh4DatabaseConnector($manager)
    {
        $manager->addConnector('ph4database', function () {
            return new Ph4DatabaseConnector($this->app['db']);
        });
    }

    /**
     * Register the queue worker.
     *
     * @return void
     */
    protected function registerWorker()
    {
        $this->app->singleton(Worker::class, function () {
            return new Ph4Worker(
                $this->app['queue'], $this->app['events'], $this->app[ExceptionHandler::class]
            );
        });
    }
}
