<?php

namespace App\Mail;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUserAutoRegistered extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var User
     */
    public $user;

    /**
     * @var ApiKey
     */
    public $apiKey;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param ApiKey $apiKey
     */
    public function __construct(User $user, ApiKey $apiKey)
    {
        $this->user = $user;
        $this->apiKey = $apiKey;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(config('app.name') .' - account registration')
            ->view('emails.account.new_user_autoreg')
            ->text('emails.account.new_user_autoreg_plain');
    }
}
