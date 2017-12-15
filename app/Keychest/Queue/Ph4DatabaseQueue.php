<?php

namespace App\Keychest\Queue;

use Illuminate\Database\DetectsDeadlocks;
use Illuminate\Database\Query\Expression;
use Illuminate\Queue\DatabaseQueue;

use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\Jobs\DatabaseJobRecord;
use Illuminate\Support\Facades\Log;

class Ph4DatabaseQueue extends DatabaseQueue implements QueueContract
{
    use DetectsDeadlocks;

    /**
     * Create an array to insert for the given job.
     *
     * @param  string|null  $queue
     * @param  string  $payload
     * @param  int  $availableAt
     * @param  int  $attempts
     * @return array
     */
    protected function buildDatabaseRecord($queue, $payload, $availableAt, $attempts = 0)
    {
        return [
            'queue' => $queue,
            'attempts' => $attempts,
            'reserved_at' => null,
            'available_at' => $availableAt,
            'created_at' => $this->currentTime(),
            'payload' => $payload,
            'version' => 0
        ];
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     * @throws \Exception|\Throwable
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        // Pops one job of the queue or return null if there is no job to process.
        //
        // In order to preserve job ordering we have to pick the first available job.
        // Workers compete for the first available job in the queue.
        //
        // Load the first available job and try to claim it.
        // During the competition it may happen another worker claims the job before we do
        // which can be easily handled and detected with optimistic locking.
        //
        // In that case we try to load another job
        // because there are apparently some more jobs in the database and pop() is supposed
        // to return such job if there is one or return null if there are no jobs so worker
        // can sleep(). Thus we have to attempt to claim jobs until there are some.
        $job = null;
        do {
            if ($job = $this->getNextAvailableJob($queue)) {

                // job is not null, try to claim it
                $jobClaimed = $this->marshalJob($queue, $job);
                if (!empty($jobClaimed)) {
                    // job was successfully claimed, return it.
                    return $jobClaimed;
                } else {
                    Log::debug('Job preempted');
                }
            }

        } while($job);
    }

    /**
     * Get the next available job for the queue.
     *
     * @param  string|null  $queue
     * @return \Illuminate\Queue\Jobs\DatabaseJobRecord|null
     */
    protected function getNextAvailableJob($queue)
    {
        $job = $this->database->table($this->table)
            ->where('queue', $this->getQueue($queue))
            ->where(function ($query) {
                $this->isAvailable($query);
                $this->isReservedButExpired($query);
            })
            ->orderBy('id', 'asc')
            ->first();

        return $job ? new DatabaseJobRecord((object) $job) : null;
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
        if (empty($job)){
            return null;
        }

        return new DatabaseJob(
            $this->container, $this, $job, $this->connectionName, $queue
        );
    }

    /**
     * Marshal the reserved job into a DatabaseJob instance.
     *
     * @param  string  $queue
     * @param  \Illuminate\Queue\Jobs\DatabaseJobRecord  $job
     * @return \Illuminate\Queue\Jobs\DatabaseJob
     */
    protected function markJobAsReserved($job)
    {
        $affected = $this->database->table($this->table)
            ->where('id', $job->id)
            ->where('version', $job->version)
            ->update([
                'reserved_at' => $job->touch(),
                'attempts' => $job->increment(),
                'version' => new Expression('version + 1'),
            ]);

        return $affected ? $job : null;
    }

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
//        $job = $this->database->table($this->table)->find($id);
//        if ($job){
//            $this->database->table($this->table)
//                ->where('id', $id)
//                ->where('version', $job->version)
//                ->delete();
//        }

        $this->database->table($this->table)
            ->where('id', $id)
            ->delete();
    }


}
