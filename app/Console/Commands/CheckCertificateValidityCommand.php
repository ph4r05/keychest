<?php

namespace App\Console\Commands;

use App\Keychest\DataClasses\ReportDataModel;
use App\Keychest\DataClasses\ValidityDataModel;
use App\Keychest\Services\AnalysisManager;
use App\Keychest\Services\EmailManager;
use App\Keychest\Services\ScanManager;
use App\Keychest\Services\ServerManager;
use App\Keychest\Utils\DataTools;
use App\Keychest\Utils\DomainTools;
use App\Mail\WeeklyNoServers;
use App\Mail\WeeklyReport;
use App\Models\EmailNews;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckCertificateValidityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-validity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check certificate validity job & send emails with warnings';

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
     * Create a new command instance.
     * @param ServerManager $serverManager
     * @param ScanManager $scanManager
     * @param EmailManager $emailManager
     * @param AnalysisManager $analysisManager
     */
    public function __construct(ServerManager $serverManager, ScanManager $scanManager,
                                EmailManager $emailManager, AnalysisManager $analysisManager)
    {
        parent::__construct();

        $this->serverManager = $serverManager;
        $this->scanManager = $scanManager;
        $this->emailManager = $emailManager;
        $this->analysisManager = $analysisManager;
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
        // User disabled reporting
        if ($user->weekly_emails_disabled) {
            return;
        }

        // Check if the last report is not too recent
        if ($user->last_email_report_sent_at &&
            Carbon::now()->subDays(7)->lessThanOrEqualTo($user->last_email_report_sent_at)) {
            return;
        }

        $this->loadUserDataAndProcess($user);
    }

    /**
     * Loads user related data and proceeds to the reporting.
     * @param User $user
     */
    protected function loadUserDataAndProcess($user){
        $md = new ValidityDataModel($user);

        // Host load, dns scans
        $this->analysisManager->loadHosts($user, $md);

        Log::info('--------------------3');
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
     * Stub function for sending a report
     * @param ValidityDataModel $md
     */
    protected function sendReport(ValidityDataModel $md){
        Log::debug('Sending email...');

        $news = $this->emailManager->loadEmailNewsToSend($md->getUser());

        // No watched servers?
        if ($md->getActiveWatches()->isEmpty()){
            $this->sendNoServers($md, $news);
            return;
        }

        $mm = $this->translateModel($md);
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
            Carbon::now()->subDays(28)->lessThanOrEqualTo($md->getUser()->last_email_no_servers_sent_at)) {
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
        $s = Mail::to($user);
        if ($enqueue){
            $s->queue($mailable
                ->onConnection('database')
                ->onQueue('emails'));
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
     * Loads all users from the DB
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function loadUsers(){
        return User::query()->get();
    }
}
