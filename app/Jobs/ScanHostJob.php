<?php

namespace App\Jobs;

use App\ScanJob;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ScanHostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $elDb;

    /**
     * Create a new job instance.
     *
     * @param ScanJob $elDb
     */
    public function __construct(ScanJob $elDb)
    {
        $this->elDb = $elDb;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Job received: ' . var_export($this->elDb));
        // TODO: host scanning logic
    }
}
