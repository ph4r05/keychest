<?php

namespace App\Mail;

use App\Keychest\DataClasses\ValidityDataModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WeeklyReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var ValidityDataModel
     */
    public $md;

    /**
     * Create a new message instance.
     *
     * @param ValidityDataModel $md
     */
    public function __construct(ValidityDataModel $md)
    {
        $this->md = $md;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->view('emails.reports.weekly')
            ->text('emails.reports.weekly_plain');
    }
}
