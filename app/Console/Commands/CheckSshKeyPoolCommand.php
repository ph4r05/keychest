<?php

namespace App\Console\Commands;

use App\Jobs\CheckSshKeyPool;
use App\Keychest\Services\CredentialsManager;
use Illuminate\Console\Command;


class CheckSshKeyPoolCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ssh-key-pool
                                {--force : override} 
                                {--sync : synchronous processing} 
                                {--one : generate only one key at a time} 
                                {--max-keys= : generate only given number of keys} 
                                {--bits= : generate RSA keys of the specific bitlength}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check SSH key pool size and generates SSH keys if not enough';

    /**
     * @var CredentialsManager
     */
    protected $credManager;

    /**
     * Create a new command instance.
     * @param CredentialsManager $credManager
     */
    public function __construct(CredentialsManager $credManager)
    {
        parent::__construct();

        $this->credManager = $credManager;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $maxKeys = $this->maxKeys();
        if ($this->doOneKeyOnly()){
            $maxKeys = 1;
        }

        // Start the new ssh pool check job.
        $job = new CheckSshKeyPool([
            'force' => $this->isForce(),
            'bits' => $this->bitSizes(),
            'max_keys' => $maxKeys,
            'sync' => $this->isSync(),
        ]);

        if ($this->isSync()){
            $job->onConnection('sync')
                ->onQueue(null);

        } else {
            $job->onConnection(config('keychest.wrk_ssh_pool_conn'))
                ->onQueue(config('keychest.wrk_ssh_pool_queue'));
        }

        dispatch($job);
        return 0;
    }

    /**
     * Synchronous processing
     */
    protected function isSync(){
        return $this->option('sync');
    }

    /**
     * Override
     * @return array|string
     */
    protected function isForce(){
        return $this->option('force');
    }

    /**
     * Generate only 1 missing key
     * @return array|string
     */
    protected function doOneKeyOnly(){
        return $this->option('one');
    }

    /**
     * Maximum number of keys to generate
     * @return array|string
     */
    protected function maxKeys(){
        return $this->option('max-keys');
    }

    /**
     * Bitsize of keys to generate
     * @return array|string
     */
    protected function bitSizes(){
        return $this->option('bits');
    }
}
