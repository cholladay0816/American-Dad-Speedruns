<?php

namespace App\Console\Commands;

use App\Mail\SuspensionLifted;
use App\Models\Suspension;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ExpireSuspensions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suspensions:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes expired suspensions.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $suspensions = Suspension::where('expiration', '<', now()->toDateTimeString())->get();
        foreach ($suspensions as $suspension)
        {
            $this->comment('Suspension #'.$suspension->id.' for '.$suspension->user->name.' expired.');
            Mail::to($suspension->user->email)
                ->queue(new SuspensionLifted($suspension->user));
            $suspension->delete();
        }
        return 0;
    }
}
