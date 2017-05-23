<?php

namespace App\Keychest\Events;

use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Events\CallQueuedListener;

class Ph4CallQueuedListener extends CallQueuedListener
{
    /**
     * Handle the queued job.
     *
     * @param  \Illuminate\Container\Container  $container
     * @return void
     */
    public function handle(Container $container)
    {
        error_log('ph4 handler!');
        $this->prepareData();

        $handler = $this->setJobInstanceIfNecessary(
            $this->job, $container->make($this->class)
        );

        call_user_func_array(
            [$handler, $this->method], $this->data
        );
    }

    /**
     * Call the failed method on the job instance.
     *
     * The event instance and the exception will be passed.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function failed($e)
    {
        $this->prepareData();

        $handler = Container::getInstance()->make($this->class);

        $parameters = array_merge($this->data, [$e]);

        if (method_exists($handler, 'failed')) {
            call_user_func_array([$handler, 'failed'], $parameters);
        }
    }

    /**
     * Unserialize the data if needed.
     *
     * @return void
     */
    protected function prepareData()
    {
        if (is_string($this->data)) {
            $this->data = unserialize($this->data);
        }
    }
}
