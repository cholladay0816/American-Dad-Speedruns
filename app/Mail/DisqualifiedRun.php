<?php

namespace App\Mail;

use App\Models\Speedrun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DisqualifiedRun extends Mailable
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
     * @param $speedruns
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.run-disqualified', ['speedrun'=>$this->speedrun])
            ->subject('Speedrun Disqualified');
    }
}
