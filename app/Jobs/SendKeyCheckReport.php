<?php

namespace App\Jobs;

use App\Keychest\DataClasses\ReportDataModel;
use App\Keychest\DataClasses\ValidityDataModel;
use App\Keychest\Services\AnalysisManager;
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
 * Class SendKeyCheckReport
 * Processes key check report, generates email from it.
 *
 * @package App\Jobs
 */
class SendKeyCheckReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Data with report
     * @var
     */
    public $reportData;

    /**
     * Options
     * @var Collection
     */
    protected $options;

    /**
     * @var EmailManager
     */
    protected $emailManager;

    /**
     * Create a new job instance.
     * @param Collection|array $options
     */
    public function __construct($report, $options = null)
    {
        $this->reportData = $report;
        $this->options = $options ? collect($options) : collect();
    }

    /**
     * Execute the job.
     * @param EmailManager $emailManager
     * @return void
     */
    public function handle(EmailManager $emailManager)
    {
        $this->emailManager = $emailManager;
        Log::info('Going to handle some report');

//        // Can email be sent to the user?
//        if (!$this->isForce() && !$this->emailManager->tryCanSendEmailToUser($this->user)){
//            return;
//        }
//
//        // Email Enabled
//        if (!$this->isForce() && $this->user->weekly_emails_disabled){
//            Log::info('User email disabled, no emails: ' . $this->user->id);
//            return;
//        }
//
//        // Double check last sent email, if job is enqueued multiple times by any chance, do not spam the user.
//        if (!$this->isForce() &&
//            $this->user->last_email_report_sent_at &&
//            Carbon::now()->subHours(2)->lessThanOrEqualTo($this->user->last_email_report_sent_at))
//        {
//            Log::info('Report for the user '
//                . $this->user->id . ' already sent recently: '
//                . $this->user->last_email_report_sent_at);
//            return;
//        }
//
//        $this->loadUserDataAndProcess($this->user);
    }

    /**
     * Stub function for sending a report
     * @param ValidityDataModel $md
     */
    protected function sendReport(ValidityDataModel $md){
//        $news = $this->emailManager->loadEmailNewsToSend($md->getUser());
//
//        // No watched servers?
//        if ($md->getActiveWatches()->isEmpty()){
//            $this->sendNoServers($md, $news);
//            return;
//        }
//
//        $mm = $this->translateModel($md);
//
//        // If report is empty, do not send it
//        if ($this->isReportEmpty($mm)){
//            Log::debug('Report is empty, not sending to:' . $this->user->id);
//            return;
//        }
//
//        Log::debug('Sending email to: ' . $this->user->id);
//        $this->sendMail($md->getUser(), new WeeklyReport($mm, $news), false);
//        $this->onReportSent($md, $news);
    }

    /**
     * Actually sends the email, either synchronously or enqueue for sending.
     * @param User $user
     * @param Mailable $mailable
     * @param bool $enqueue
     */
    protected function sendMail(User $user, Mailable $mailable, $enqueue=false){
        $userObj = $user;
        if (!empty($user->notification_email)){
            $userObj = new \stdClass();
            $userObj->email = $user->notification_email;
            $userObj->name = $user->name;
        }

        $s = Mail::to($userObj);
        if ($enqueue){
            $s->queue($mailable
                ->onConnection(config('keychest.wrk_weekly_emails_conn'))
                ->onQueue(config('keychest.wrk_weekly_emails_queue')));
        } else {
            $s->send($mailable);
        }
    }
}
