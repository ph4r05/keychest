<?php

namespace App\Jobs;

use App\Keychest\Queue\JsonJob;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class TesterJob implements ShouldQueue, JsonJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var
     */
    public $elJson;

    /**
     * Create a new job instance.
     *
     * @param $elJson
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
        // won't be executed as this job is processed on the python backend
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
