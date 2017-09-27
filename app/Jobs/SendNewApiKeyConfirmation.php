<?php

namespace App\Jobs;

use App\Keychest\Services\EmailManager;
use App\Keychest\Services\Exceptions\CannotSendEmailException;
use App\Keychest\Services\UserManager;
use App\Mail\NewApiKeyAutoRegistered;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendNewUserConfirmation
 * Sends new user confirmation email when created by API / automated way
 *
 * @package App\Jobs
 */
class SendNewApiKeyConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * User to notify
     * @var User
     */
    public $user;

    /**
     * Api key caused this
     * @var ApiKey
     */
    public $apiKey;

    /**
     * Api key caused this
     * @var Request
     */
    public $request;

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
     * @var UserManager
     */
    protected $userManager;

    /**
     * Create a new job instance.
     * @param User $user
     * @param ApiKey $apiKey
     * @param null $request
     * @param Collection|array $options
     */
    public function __construct(User $user, ApiKey $apiKey, $request = null, $options = null)
    {
        $this->user = $user;
        $this->apiKey = $apiKey;
        $this->request = $request;
        $this->options = $options ? collect($options) : collect();
    }

    /**
     * Execute the job.
     * @param EmailManager $emailManager
     * @param UserManager $userManager
     * @return void
     */
    public function handle(EmailManager $emailManager, UserManager $userManager)
    {
        $this->emailManager = $emailManager;
        $this->userManager = $userManager;

        // Can email be sent to the user?
        if (!$this->isForce()){
            try {
                $this->emailManager->canSendEmailToUser($this->user);
            } catch (CannotSendEmailException $e) {
                // NOK, we are not sending
                return;
            }
        }

        $this->sendEmail();
    }

    /**
     * Sends the email to the user
     * @param bool $enqueue
     */
    protected function sendEmail($enqueue=false){
        Log::debug('Sending email to: ' . $this->user->id);

        $userObj = $this->user;
        if (!empty($this->user->notification_email)){
            $userObj = new \stdClass();
            $userObj->email = $this->user->notification_email;
            $userObj->name = $this->user->name;
        }

        $mailable = new NewApiKeyAutoRegistered($this->user, $this->apiKey);

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
     * Force send?
     * @return mixed
     */
    protected function isForce(){
        return $this->options->get('force', false);
    }
}
