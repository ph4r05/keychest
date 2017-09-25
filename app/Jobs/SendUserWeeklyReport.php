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
use App\User;
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
 * Class SendUserWeeklyReport
 * Per-user weekly report job, submitted from the weekly reporting job
 *
 * Benefits:
 *  - avoid segfaults with large payloads for email model (only user ID is serialized as job data)
 *  - more scalable as there might be many workers processing independent per-user jobs
 *
 * @package App\Jobs
 */
class SendUserWeeklyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * User to analyze
     * @var User
     */
    protected $user;

    /**
     * Options
     * @var Collection
     */
    protected $options;

    /**
     * Scan manager
     * @var ScanManager
     */
    protected $scanManager;

    /**
     * @var ServerManager
     */
    protected $serverManager;

    /**
     * @var EmailManager
     */
    protected $emailManager;

    /**
     * @var AnalysisManager
     */
    protected $analysisManager;

    /**
     * Create a new job instance.
     * @param User $user
     * @param Collection|array $options
     */
    public function __construct(User $user, $options = null)
    {
        $this->user = $user;
        $this->options = $options ? collect($options) : collect();
    }

    /**
     * Execute the job.
     * @param ServerManager $serverManager
     * @param ScanManager $scanManager
     * @param EmailManager $emailManager
     * @param AnalysisManager $analysisManager
     * @return void
     */
    public function handle(ServerManager $serverManager, ScanManager $scanManager,
                           EmailManager $emailManager, AnalysisManager $analysisManager)
    {
        $this->serverManager = $serverManager;
        $this->scanManager = $scanManager;
        $this->emailManager = $emailManager;
        $this->analysisManager = $analysisManager;

        // Deleted
        if (!$this->isForce() && !empty($this->user->deleted_at)){
            Log::info('User deleted, no emails: ' . $this->user->id);
            return;
        }

        // Closed
        if (!$this->isForce() && !empty($this->user->closed_at)){
            Log::info('User closed, no emails: ' . $this->user->id);
            return;
        }

        // Email Enabled
        if (!$this->isForce() && $this->user->weekly_emails_disabled){
            Log::info('User email disabled, no emails: ' . $this->user->id);
            return;
        }

        // Not verified
        if (!$this->isForce() && (!empty($this->user->auto_created_at) && empty($this->user->verified_at))){
            Log::info('User not verified, no emails: ' . $this->user->id);
            return;
        }

        // Double check last sent email, if job is enqueued multiple times by any chance, do not spam the user.
        if (!$this->isForce() &&
            $this->user->last_email_report_sent_at &&
            Carbon::now()->subHours(2)->lessThanOrEqualTo($this->user->last_email_report_sent_at))
        {
            Log::info('Report for the user '
                . $this->user->id . ' already sent recently: '
                . $this->user->last_email_report_sent_at);
            return;
        }

        $this->loadUserDataAndProcess($this->user);
    }

    /**
     * Loads user related data and proceeds to the reporting.
     * @param User $user
     */
    protected function loadUserDataAndProcess($user){
        $md = new ValidityDataModel($user);

        // Host load, dns scans
        $this->analysisManager->loadHosts($user, $md);

        Log::info('--------------------');
        $this->analysisManager->loadCerts($md);

        Log::info(var_export($md->getCerts()->count(), true));
        $this->analysisManager->loadWhois($md);

        // Processing section
        $this->analysisManager->processExpiring($md);

        // 2. incidents
        // TODO: ...

        $this->sendReport($md);
    }

    /**
     * Translates model from ValidityDataModel to ReportDataModel
     * @param ValidityDataModel $md
     * @return ReportDataModel
     */
    protected function translateModel(ValidityDataModel $md){
        $mm = new ReportDataModel($md->getUser());
        $mm->setNumActiveWatches($md->getActiveWatches()->count());
        $mm->setNumAllCerts($md->getNumAllCerts());
        $mm->setNumCertsActive($md->getNumCertsActive());

        $mm->setCertExpired($this->thinCertsModel($md->getCertExpired()));
        $mm->setCertExpire7days($this->thinCertsModel($md->getCertExpire7days()));
        $mm->setCertExpire28days($this->thinCertsModel($md->getCertExpire28days()));
        return $mm;
    }

    /**
     * Removes unnecessary data from the certs model - removes the serialization overhead.
     * @param Collection $certs
     * @return Collection
     */
    protected function thinCertsModel(Collection $certs){
        if (!$certs){
            return collect();
        }

        return $certs->map(function($item, $key){
            if ($item->tls_watches){
                $item->tls_watches->map(function($item2, $key2){
                    $item2->dns_scan = collect();
                    $item2->tls_scans = collect();
                    return $item2;
                });
            }
            return $item;
        });
    }

    /**
     * Returns true if the report is empty - could bother the user.
     * @param ReportDataModel $mm
     * @return bool
     */
    protected function isReportEmpty(ReportDataModel $mm){
        return $mm->getCertExpire28days()->isEmpty()
            && $mm->getCertExpire7days()->isEmpty();
    }

    /**
     * Stub function for sending a report
     * @param ValidityDataModel $md
     */
    protected function sendReport(ValidityDataModel $md){
        $news = $this->emailManager->loadEmailNewsToSend($md->getUser());

        // No watched servers?
        if ($md->getActiveWatches()->isEmpty()){
            $this->sendNoServers($md, $news);
            return;
        }

        $mm = $this->translateModel($md);

        // If report is empty, do not send it
        if ($this->isReportEmpty($mm)){
            Log::debug('Report is empty, not sending to:' . $this->user->id);
            return;
        }

        Log::debug('Sending email to: ' . $this->user->id);
        $this->sendMail($md->getUser(), new WeeklyReport($mm, $news), false);
        $this->onReportSent($md, $news);
    }

    /**
     * Sends no servers yet message
     * @param ValidityDataModel $md
     * @param Collection $news
     */
    protected function sendNoServers(ValidityDataModel $md, Collection $news){
        // Check if the last report is not too recent
        if ($md->getUser()->last_email_no_servers_sent_at &&
            Carbon::now()->subDays(28 * 3)->lessThanOrEqualTo($md->getUser()->last_email_no_servers_sent_at)) {
            return;
        }

        $mm = $this->translateModel($md);
        $this->sendMail($md->getUser(), new WeeklyNoServers($mm, $news), false);

        $md->getUser()->last_email_no_servers_sent_at = Carbon::now();
        $this->onReportSent($md, $news);
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

    /**
     * Update user last report sent date.
     * @param ValidityDataModel $md
     * @param Collection $news
     */
    protected function onReportSent(ValidityDataModel $md, Collection $news){
        $user = $md->getUser();
        $user->last_email_report_sent_at = Carbon::now();
        $user->save();

        // Save sent news.
        $this->emailManager->associateNewsToUser($user, $news);
    }

    /**
     * Force send?
     * @return mixed
     */
    protected function isForce(){
        return $this->options->get('force', false);
    }
}
