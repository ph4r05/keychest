<?php

namespace App\Jobs;

use App\Keychest\Queue\JsonJob;
use App\ScanJob;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ScanHostJob implements ShouldQueue, JsonJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $elDb;
    protected $elJson;

    /**
     * Create a new job instance.
     *
     * @param ScanJob $elDb
     */
    public function __construct(ScanJob $elDb, $elJson)
    {
        $this->elDb = $elDb;
        $this->elJson = $elJson;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Job received: ' . var_export($this->elDb, true));
        // TODO: host scanning logic
    }

    /**
     * Returns json interpretation
     * @return array
     */
    public function toJson()
    {
        return $this->elJson;
    }
}
