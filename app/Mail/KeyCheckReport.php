<?php

namespace App\Mail;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class KeyCheckReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var
     */
    public $recipientName;

    /**
     * @var
     */
    public $pgpKeys;

    /**
     * @var
     */
    public $smimeKeys;

    /**
     * @var
     */
    public $allSafe;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(config('app.name') .' - ROCAHelp key check')
            ->markdown('emails.keytest.report');
    }
}
