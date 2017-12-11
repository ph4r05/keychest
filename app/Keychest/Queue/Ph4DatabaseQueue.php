<?php

namespace App\Keychest\Queue;




use Illuminate\Queue\DatabaseQueue;

use Illuminate\Contracts\Queue\Queue as QueueContract;
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
            //if ($this->database->table($this->table)->lockForUpdate()->find($id)) {
            //    $this->database->table($this->table)->where('id', $id)->delete();
            //}

            // Find, lock, delete mechanism is abandoned to avoid deadlocks.
            // Trying to directly remove the job.
            $this->database->table($this->table)->where('id', $id)->delete();
        }, 5);
    }

}
