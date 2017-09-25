<?php

namespace App\Console\Commands;

use App\Jobs\SendUserWeeklyReport;


use App\Keychest\Utils\DataTools;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;


class CheckCertificateValidityCommand extends Command
{
    /**
     * Scheduling precision in minutes.
     * Means this command should be executed by the scheduler each X or X/2 (to be precise) minutes.
     * Used for computing the time to send an email.
     */
    const SCHEDULE_PRECISION_MIN = 10;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-validity 
                                {--sync : synchronous processing} 
                                {--ignore-target : do not wait for the target day}
                                {--force-send : force sending}
                                {--id=* : user IDs to pick specifically}';

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
        $filter = collect($this->userFilter());

        foreach($users->all() as $user){
            if ($filter->isNotEmpty() && !$filter->contains($user->id)){
                continue;
            }

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
        if (!$this->isForce() && !empty($user->deleted_at)){
            Log::info('User deleted, no emails: ' . $user->id);
            return;
        }

        if (!$this->isForce() && !empty($user->closed_at)){
            Log::info('User closed, no emails: ' . $user->id);
            return;
        }

        // User disabled reporting.
        if ($user->weekly_emails_disabled) {
            return;
        }

        // Not verified
        if (!$this->isForce() && (!empty($user->auto_created_at) && empty($user->verified_at))){
            Log::info('User not verified, no emails: ' . $user->id);
            return;
        }

        // enqueued recently, let it process.
        if (!$this->isForce() &&
            $user->last_email_report_enqueued_at &&
            Carbon::now()->subDays(1)->lessThanOrEqualTo($user->last_email_report_enqueued_at))
        {
            return;
        }

        // Weekly basis / scheduled time
        if (!$this->isForce() && !$this->shouldSendWeeklyNow($user)){
            return;
        }

        // Start the new user job
        $job = new SendUserWeeklyReport($user, ['force' => $this->isForce()]);
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

        Log::debug('Enqueued weekly report for: ' . $user->id);
    }

    /**
     * Loads all users from the DB
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function loadUsers(){
        return User::query()->get();
    }

    /**
     * Decides whether to send the weekly report now.
     * Assume this is being asked each SCHEDULE_PRECISION_MIN.
     *
     * @param $user
     * @return bool
     */
    protected function shouldSendWeeklyNow($user){
        // General policy is to send weekly summaries on Monday 9:00 AM local time of the user.
        // Target dayOfWeek, hour and minute could be defined also per-account.
        $targetDay = Carbon::MONDAY;
        $targetHour = 8;
        $targetMinute = 0;

        // Compute criteria for sending
        $tz = DataTools::sanitizeTimezone($user->timezone);
        $nowUserLocal = Carbon::now($tz);
        $nowServerLocal = Carbon::now('UTC');

        $lastSentSL = $user->last_email_report_sent_at;  // server local
        $firstEmail = $lastSentSL ? false : true;
        $diffLastSentSec = $firstEmail ? false : $nowServerLocal->diffInSeconds($lastSentSL);
        $diffLastSentDays = $diffLastSentSec / 3600.0 / 24.0;

        // If sent just recently - do not send again, 1 day boundary
        if (!$firstEmail && $diffLastSentDays <= 2.0){
            return false;
        }

        if ($this->shouldIgnoreTarget()){
            return true;
        }

        // Define target time for sending in the user timezone.
        $targetUserLocal = Carbon::now($tz);
        $targetUserLocal->setTime($targetHour, $targetMinute);
        if ($targetUserLocal->dayOfWeek != $targetDay){
            // Carbon->next(Carbon::MONDAY) sucks, does not work well
            // It just changes time, but the time is in the local time zone (BUG)
            $daysToAdd = $targetUserLocal->dayOfWeek < $targetDay ?
                     $targetDay  - $targetUserLocal->dayOfWeek :
                (7 + $targetDay) - $targetUserLocal->dayOfWeek;

            $targetUserLocal->addDays($daysToAdd);
        }

        // Distance from the target
        // If target is ahead (positive value), wait till we reach the boundary
        $diffFromTargetHrs = $nowUserLocal->diffInSeconds($targetUserLocal, false) / 3600.0; // == target - nowUserLocal
        if ($diffFromTargetHrs > 0){
            return false;  // target will come soon
        }

        if ($diffFromTargetHrs < -12.0){
            return false;  // window for sending email passed
        }

        return true;
    }

    /**
     * Synchronous processing
     */
    protected function isSync(){
        return $this->option('sync');
    }

    /**
     * Send no matter what
     * @return array|string
     */
    protected function isForce(){
        return $this->option('force-send');
    }

    /**
     * Should ignore target day and just send?
     * @return array|string
     */
    protected function shouldIgnoreTarget(){
        return $this->option('ignore-target');
    }

    /**
     * Sending only to specific users
     * @return array|string
     */
    protected function userFilter(){
        return $this->option('id');
    }
}
