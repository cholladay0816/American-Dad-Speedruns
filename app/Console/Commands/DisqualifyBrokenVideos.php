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
        $checker=app(\App\Services\VideoAvailability::class);
        $runs=Speedrun::where('verified',1)->whereDoesntHave('disqualification')->get();
        $results=[];$healthy=false;$providerFailure=false;
        foreach($runs as $run){
            $result=$checker->inspect($run->video_id());$results[$run->id]=$result;
            $healthy=$healthy || $result['state']==='available';
            $providerFailure=$providerFailure || $result['state']==='provider_failure';
        }
        // No mutations until the batch is assessed. A uniform auth/not-found failure
        // or a provider-wide quota/transport failure must not disqualify every run.
        if(!$healthy || $providerFailure){$this->warn('Video scan deferred: provider health is not established.');return 0;}
        foreach($runs as $run){
            $result=$results[$run->id];
            if($result['state']!=='candidate')continue;
            \Illuminate\Support\Facades\Log::info('Video availability evidence',['run_id'=>$run->id]+$result);
            $run->disqualify($checker->reason($result),$result['url']);
        }
        return 0;
    }
}
