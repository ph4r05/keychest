<?php

namespace App\Keychest\Queue;




use Illuminate\Queue\DatabaseQueue;

use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Support\Facades\Log;

class Ph4DatabaseQueue extends DatabaseQueue implements QueueContract
{
    /**
     * Delete a reserved job from the queue.
     * https://github.com/laravel/framework/issues/7046
     *
     * @param  string $queue
     * @param  string $id
     * @return void
     * @throws \Exception|\Throwable
     */
    public function deleteReserved($queue, $id)
    {
        $this->database->transaction(function() use($queue, $id){
            if ($this->database->table($this->table)->lockForUpdate()->find($id)) {
                $this->database->table($this->table)->where('id', $id)->delete();
            }

        }, 5);
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        try {
            return $this->database->transaction(function () use ($queue) {
                if ($job = $this->getNextAvailableJob($queue)) {
                    return $this->marshalJob($queue, $job);
                }

                return null;
            }, 5);

        } catch (\Exception $e) {
            Log::error('Exception in Ph4DatabaseQueue::pop()');
            Log::info($e);
            throw new \RuntimeException($e);
        }
    }

    /**
     * Marshal the reserved job into a DatabaseJob instance.
     *
     * @param  string  $queue
     * @param  \Illuminate\Queue\Jobs\DatabaseJobRecord  $job
     * @return \Illuminate\Queue\Jobs\DatabaseJob
     */
    protected function marshalJob($queue, $job)
    {
        $job = $this->markJobAsReserved($job);
        return new DatabaseJob(
            $this->container, $this, $job, $this->connectionName, $queue
        );
    }

}
