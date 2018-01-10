<?php

namespace App\Console\Commands;

use App\Jobs\CheckSshKeyPool;
use App\Keychest\Services\CredentialsManager;
use App\Models\SshKey;
use Illuminate\Console\Command;


class DumpSshKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ssh-key-dump
                                {--id= : SSH key id} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dumps private SSH key in a cleartext - for Ansible testing.';

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
        $id = $this->option('id');
        if (empty($id)){
            $this->error('ID is empty');
            return 1;
        }

        $sshKey = SshKey::query()->where('id', $id)->first();
        if (empty($sshKey)){
            $this->info('Key with ID ' . $id . ' does not exist');
            return 2;
        }

        $this->line($sshKey->priv_key);
        return 0;
    }


}
