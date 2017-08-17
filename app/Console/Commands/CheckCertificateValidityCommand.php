<?php

namespace App\Console\Commands;

use App\Jobs\SendUserWeeklyReport;
use App\Keychest\Services\AnalysisManager;
use App\Keychest\Services\EmailManager;
use App\Keychest\Services\ScanManager;
use App\Keychest\Services\ServerManager;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;


class CheckCertificateValidityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-validity {--sync : synchronous processing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check certificate validity job & send emails with warnings';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = $this->loadUsers();
        foreach($users->all() as $user){
            $this->processUser($user);
        }

        return 0;
    }

    /**
     * Check for one particular user.
     * @param User $user
     */
    protected function processUser($user)
    {
        // User disabled reporting.
        if ($user->weekly_emails_disabled) {
            return;
        }

        // Check if the last report is not too recent.
        if ($user->last_email_report_sent_at &&
            Carbon::now()->subDays(7)->lessThanOrEqualTo($user->last_email_report_sent_at)) {
            return;
        }

        // enqueued recently, let it process.
        if ($user->last_email_report_enqueued_at &&
            Carbon::now()->subDays(1)->lessThanOrEqualTo($user->last_email_report_enqueued_at)){
            return;
        }

        // Start the new user job
        $job = new SendUserWeeklyReport($user);
        if ($this->isSync()){
            $job->onConnection('sync')
                ->onQueue(null);

        } else {
            $job->onConnection(config('keychest.wrk_weekly_report_conn'))
                ->onQueue(config('keychest.wrk_weekly_report_queue'));
        }

        dispatch($job);

        $user->last_email_report_enqueued_at = Carbon::now();
        $user->save();
    }

    /**
     * Loads all users from the DB
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function loadUsers(){
        return User::query()->get();
    }

    /**
     * Synchronous processing
     */
    protected function isSync(){
        return $this->option('sync');
    }
}
