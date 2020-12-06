<?php

namespace App\Console\Commands;

use App\Models\Banner;
use Illuminate\Console\Command;

class DeleteBanners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banner:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes all expired banners';

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
        $banners = Banner::where('expiration', '<', now()->toDateTimeString())->get();
        $this->comment('Cleaning up ' . $banners->count() . ' banners.');
        foreach ($banners as $banner)
        {
            $this->comment($banner->title . ' is expired, removing..');
            $banner->delete();
        }
        return 0;
    }
}
