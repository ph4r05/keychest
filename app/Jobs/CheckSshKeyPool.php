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
 * Class CheckSshKeyPool
 * Checks SSH key pool state, generates missing keys.
 *
 * @package App\Jobs
 */
class CheckSshKeyPool implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of keys to generate in one batch job.
     */
    const KEY_BATCH_CHUNK_SIZE = 10;

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
     * Internal deficit of keys to generate.
     * @var
     */
    protected $deficit;

    /**
     * Number of keys generated already.
     * @var int
     */
    protected $keysGenerated = 0;

    /**
     * Create a new job instance.
     * @param Collection|array $options
     */
    public function __construct($options = null)
    {
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

        // Expire old SSH keys so we don't store unused pregenerated keys for a long time.
        // We want some degree of key freshness when assigned to a new host.
        $expiredKeys = $this->credManager->expireOldSshKeys();
        if ($expiredKeys > 0){
            Log::info('Deleted old SSH keys: ' . $expiredKeys);
        }

        // Inspect current state of the SSH key pool. Returns the array bitlen -> missing keys to generate.
        $deficit = $this->credManager->getSshKeyPoolDeficit();
        $maxKeys = $this->maxNumKeys();

        $targetSize = $this->bits();
        if ($targetSize > 0) {
            $deficit = isset($deficit[$targetSize]) ? [$targetSize => $deficit[$targetSize]] : null;
        }

        if (empty($deficit)){
            return;
        }

        $this->deficit = $deficit;
        $this->keysGenerated = 0;
        while($maxKeys <= 0 || $this->keysGenerated < $maxKeys){
            $keyGenBefore = $this->keysGenerated;
            $this->generateBatch();

            // Simple termination - if nothing has been done, terminate.
            if ($keyGenBefore == $this->keysGenerated){
                break;
            }
        }
    }

    /**
     * Generates one batch for given bit lengths in the $this->deficit.
     * E.g. if batch size is 5 and deficit is [1024=>100, 2048=>100, 4096=>100] the process will generate
     * 5x1024, 5x2048, 5x4096 (assuming maxKeys is -1)
     */
    protected function generateBatch(){
        $maxKeys = $this->maxNumKeys();

        $deficit = $this->deficit; // own copy for iteration
        foreach($deficit as $bitLen => $numKeys) {
            $bitLen = intval($bitLen);

            // Generate SSH keys in independent jobs, using batches.
            if ($maxKeys > 0 && $this->keysGenerated >= $maxKeys) {
                break;
            }

            $numKeysToGenerate = $maxKeys > 0 ?
                min($numKeys, $maxKeys - $this->keysGenerated) : $numKeys;

            $numKeysToGenerateBatch = self::KEY_BATCH_CHUNK_SIZE > 0 ?
                min($numKeysToGenerate, self::KEY_BATCH_CHUNK_SIZE) : $numKeysToGenerate;

            if ($numKeysToGenerateBatch <= 0) {
                continue;
            }

            $this->keysGenerated += $numKeysToGenerateBatch;
            $this->deficit[$bitLen] -= $numKeysToGenerateBatch;

            $workSize = [$bitLen => $numKeysToGenerateBatch];
            $job = new GenerateSshPoolKeys($workSize);

            if ($this->isSync()) {
                $job->onConnection('sync')
                    ->onQueue(null);

            } else {
                $job->onConnection(config('keychest.wrk_ssh_pool_gen_conn'))
                    ->onQueue(config('keychest.wrk_ssh_pool_gen_queue'));
            }

            dispatch($job);
        }
    }

    /**
     * Force send?
     * @return mixed
     */
    protected function isForce(){
        return $this->options->get('force', false);
    }

    /**
     * Should be generating jobs generated in sync with pool check?
     * @return mixed
     */
    protected function isSync(){
        return $this->options->get('sync', false);
    }

    /**
     * Maximal number of keys to generate
     */
    protected function maxNumKeys(){
        return intval($this->options->get('max_keys', -1));
    }

    /**
     * Generate only particular bit sizes
     */
    protected function bits(){
        return intval($this->options->get('bits', 0));
    }

}
