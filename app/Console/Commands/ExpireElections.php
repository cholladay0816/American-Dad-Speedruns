<?php

namespace App\Console\Commands;

use App\Models\Election;
use Illuminate\Console\Command;

class ExpireElections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'election:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expires elections, solidifying the results.';

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
        $elections = Election::where('active', 1)->where('expiration', '<', now()->toDateTimeString())->get();
        foreach ($elections as $election)
        {
            if($election->total() == 0)
            {
                $this->comment('Election #'.$election->id.' received 0 votes, deleting..');
                $election->delete();
                continue;
            }
            if($election->negative() > $election->positive())
            {
                $election->speedrun->disqualify('The Council has disqualified this run by vote.', url('/elections/'.$election->id));
            }

            $election->active = '0';
            $election->save();
        }
        return 0;
    }
}
