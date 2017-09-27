<?php

namespace App\Mail;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewApiKeyAutoRegistered extends Mailable
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
            ->subject(config('app.name') .' - new API key')
            ->view('emails.account.new_api_key_autoreg')
            ->text('emails.account.new_api_key_autoreg_plain');
    }
}
