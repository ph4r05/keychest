<?php

namespace App\Jobs;

use App\Keychest\Queue\JsonJob;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class AutoAddSubsJob implements ShouldQueue, JsonJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $elJson;

    /**
     * Create a new job instance.
     *
     * @param mixed $elJson
     */
    public function __construct($elJson)
    {
        $this->elJson = $elJson;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Job received: ' . var_export($this->elJson, true));
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
