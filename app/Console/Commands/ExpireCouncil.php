<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpired;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ExpireCouncil extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'council:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expires all council members with unpaid subscriptions';

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
        $council = Role::where('name', 'council')->first();
        $members = $council->users->unique();
        foreach ($members as $user)
        {
            if ($user->subscribed('default'))
            {
                $this->comment($user->name . ' no longer has a subscription, removing..');
                $council->users()->detach($user);
                Mail::to($user->email)->queue(new SubscriptionExpired($user));
            }
            else
            {
                $this->comment($user->name . ' still has a valid subscription.');
            }
        }
        return 0;
    }
}
