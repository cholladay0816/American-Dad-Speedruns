<?php

namespace App\Mail;

use App\Models\Speedrun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DisqualifiedRuns extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($speedruns)
    {
        $this->speedruns = $speedruns;
    }

    /**
     * Build the message.
     *
     * @param $speedruns
     * @return $this
     */
    public function build()
    {
        return $this->from('noreply@'.config('APP_DOMAIN'))
            ->view('email.runs-disqualified', ['speedruns'=>$this->speedruns]);
    }
}
