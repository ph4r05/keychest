<?php

namespace App\Jobs;

use App\Keychest\DataClasses\ReportDataModel;
use App\Keychest\DataClasses\ValidityDataModel;
use App\Keychest\Services\AnalysisManager;
use App\Keychest\Services\CredentialsManager;
use App\Keychest\Services\EmailManager;
use App\Keychest\Services\ScanManager;
use App\Keychest\Services\ServerManager;
use App\Mail\WeeklyNoServers;
use App\Mail\WeeklyReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Class GenerateSshPoolKeys
 * Generates the SSH pool keys.
 *
 * @package App\Jobs
 */
class GenerateSshPoolKeys implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Work assignment [bitsize => numKeys]
     * @var array
     */
    protected $workSize;

    /**
     * Options
     * @var Collection
     */
    protected $options;

    /**
     * Credentials manager
     * @var CredentialsManager
     */
    protected $credManager;

    /**
     * Create a new job instance.
     * @param Collection|array $workSize [bitsize => numKeys]
     * @param Collection|array $options
     */
    public function __construct($workSize, $options = null)
    {
        $this->workSize = $workSize;
        $this->options = $options ? collect($options) : collect();
    }

    /**
     * Execute the job.
     * @param CredentialsManager $credManager
     * @return void
     */
    public function handle(CredentialsManager $credManager)
    {
        $this->credManager = $credManager;

        foreach ($this->workSize as $bitLen => $numKeys){
            for($cKey = 0; $cKey < $numKeys; $cKey++){
                $this->credManager->generateSshPoolKey($bitLen);
            }
        }
    }
}

