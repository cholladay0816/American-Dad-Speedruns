<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ElectionStarted extends Mailable
{
    use Queueable, SerializesModels;

    public $election;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($election, $user)
    {
        $this->election = $election;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.election-started', ['election'=>$this->election, 'user'=>$this->user]);
    }
}
