<?php

namespace Database\Seeders;

use App\Models\Ability;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $m_users = \App\Models\Ability::firstOrCreate(['name'=>'manage_users','title'=>'Manage Users']);
        $m_runs = \App\Models\Ability::firstOrCreate(['name'=>'manage_speedruns','title'=>'Manage Speedruns']);
        $v_admin = \App\Models\Ability::firstOrCreate(['name'=>'view_admin','title'=>'View Administrator Dashboard']);

        $admin = \App\Models\Role::firstOrCreate(['name'=>'administrator','title'=>'Administrator']);
        $mod = \App\Models\Role::firstOrCreate(['name'=>'moderator','title'=>'Moderator']);

        $admin->abilities()->sync([$m_users->id, $m_runs->id, $v_admin->id]);

        $mod->abilities()->sync([$m_runs->id, $v_admin->id]);

        \App\Models\Category::firstOrCreate(['url'=>asset('img/american_dad_speedrun_logo.png'),'title'=>'Any%','description'=>'Simple: beat the game from start to finish by any means available.']);
        \App\Models\Category::firstOrCreate(['url'=>asset('img/american_joe_speedrun_logo.png'),'title'=>'Joe%','description'=>'Same rules as Any%, but you must use the Joe character.']);
        \App\Models\Category::firstOrCreate(['url'=>asset('img/american_dad_speedrun_logo.png'),'title'=>'New%','description'=>'New game content is allowed for this category.']);
        \App\Models\Category::firstOrCreate(['url'=>asset('img/american_joe_speedrun_logo.png'),'title'=>'JoeSwap%','description'=>'I don\'t remember what this one is.']);
        \App\Models\Category::firstOrCreate(['url'=>asset('img/american_dad_speedrun_logo.png'),'title'=>'Glitchless%','description'=>'No glitches or exploits allowed, play the game how God intended.']);
        \App\Models\Category::firstOrCreate(['url'=>asset('img/american_dad_speedrun_logo.png'),'title'=>'MacFarlane%','description'=>'The entire MacFarlane anthology back to back.']);
        \App\Models\Category::firstOrCreate(['url'=>asset('img/american_dad_speedrun_logo.png'),'title'=>'TAS','description'=>'Tool-Assisted Speedruns: first they came for the jobs, now they\'re after the world record.']);

        \App\Models\Category::firstOrCreate(['title'=>'DOS', 'description'=>'Development Build, never intended to be used for anything other than testing until the source code was leaked online mid-development. People have been practicing their runs on this buggy version since.']);
        \App\Models\Category::firstOrCreate(['title'=>'PC', 'description'=>'Port of Gamecube Release, most popular yet controversial speedrun category for its precision and moddability.']);
        \App\Models\Category::firstOrCreate(['title'=>'SourcePort: GZDad', 'description'=>'One of the two most popular SourcePorts of the PC release, improves stability and supports 64-bit Operating Systems.']);
        \App\Models\Category::firstOrCreate(['title'=>'SourcePort: DadSpasm', 'description'=>'Second most popular SourcePort, improves stability and allows for easy modification by devs and modders.']);
        \App\Models\Category::firstOrCreate(['title'=>'Gamecube', 'description'=>'Original Release, considered universally to be the most stable release. This version is also the most canonically accurate.']);
        \App\Models\Category::firstOrCreate(['title'=>'SNES', 'description'=>'Port of Gamecube release, backwards compatible due to the game releasing between console generations.']);
        \App\Models\Category::firstOrCreate(['title'=>'Playstation 2', 'description'=>'PS2 Port of XBox release.']);
        \App\Models\Category::firstOrCreate(['title'=>'XBox', 'description'=>'This platform was very popular at launch, but due to instability and framerate issues, this version performs poorly in speedruns.']);
        \App\Models\Category::firstOrCreate(['title'=>'Wii', 'description'=>'Port of Gamecube Release.']);
        \App\Models\Category::firstOrCreate(['title'=>'Nintendo 64', 'description'=>'Original Release, backwards compatible due to the game releasing between console generations.']);
        \App\Models\Category::firstOrCreate(['title'=>'Smart Fridge', 'description'=>'People say American Dad can run on anything, and this platform proves it. The SourcePort DadSpasm has been heavily modded to support a smart fridge\'s hardware.']);
        \App\Models\Category::firstOrCreate(['title'=>'3DS', 'description'=>'For the game\'s 5 year anniversary, the devs ported it to the 3DS to be enjoyed on the go.']);
        \App\Models\Category::firstOrCreate(['title'=>'Game and Watch', 'description'=>'Primitive precursor to the 2005 release.']);

    }
}
