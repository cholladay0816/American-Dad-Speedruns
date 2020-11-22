<?php

namespace App\Console\Commands;

use App\Models\Speedrun;
use Illuminate\Console\Command;

class DisqualifyBrokenVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'speedruns:disqualifybrokenvideos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disqualifies videos that have no working URL';

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
        //Get all verified runs
        $speedruns = Speedrun::where('verified',1)->get()->filter(function($run) {return !$run->disqualified();});
        $disqualified = [];
        foreach ($speedruns as $speedrun)
        {
            //Only show qualifying speedruns
            if($speedrun->disqualified())
                continue;

            //If the video is not found on YouTube, disqualify it.
            if(!$speedrun->videoExists())
            {
                $this->info('Speedrun #'.$speedrun->id . ' is no longer visible, disqualifying...');
                $speedrun->disqualify('YouTube video no longer listed.');
                array_push($disqualified, $speedrun);
            }
        }
        //If a run was disqualified, send an update email informing staff.

        return 0;
    }
}
