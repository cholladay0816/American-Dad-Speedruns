<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RunApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $speedrun;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($speedrun)
    {
        $this->speedrun = $speedrun;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.run-approved')
            ->subject('Speedrun Approved!');
    }
}
