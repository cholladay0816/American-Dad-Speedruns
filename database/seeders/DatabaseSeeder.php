<?php

namespace Database\Seeders;

use App\Models\Ability;
use App\Models\Category;
use App\Models\Platform;
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
        $m_users = Ability::firstOrCreate(['name'=>'manage_users','title'=>'Manage Users']);
        $m_runs = Ability::firstOrCreate(['name'=>'manage_speedruns','title'=>'Manage Speedruns']);
        $m_banners = Ability::firstOrCreate(['name'=>'manage_banners','title'=>'Manage Banners']);
        $v_admin = Ability::firstOrCreate(['name'=>'view_admin','title'=>'View Administrator Dashboard']);

        $admin = Role::firstOrCreate(['name'=>'administrator','title'=>'Administrator']);
        $mod = Role::firstOrCreate(['name'=>'moderator','title'=>'Moderator']);

        $admin->abilities()->sync([$m_users->id, $m_runs->id, $m_banners, $v_admin->id]);

        $mod->abilities()->sync([$m_runs->id, $m_banners, $v_admin->id]);

        Category::firstOrCreate(['name'=>'any','title'=>'Any%','description'=>'Simple: beat the game from start to finish by any means available.','url'=>url('img/american_dad_speedrun_logo.png')]);
        Category::firstOrCreate(['name'=>'joe','title'=>'Joe%','description'=>'Same rules as Any%, but you must use the Joe character.','url'=>url('img/american_joe_speedrun_logo.png')]);
        Category::firstOrCreate(['name'=>'new','title'=>'New%','description'=>'New game content is allowed for this category.','url'=>url('img/american_dad_speedrun_logo.png')]);
        Category::firstOrCreate(['name'=>'joeswap','title'=>'JoeSwap%','description'=>'I don\'t remember what this one is.','url'=>url('img/american_joe_speedrun_logo.png')]);
        Category::firstOrCreate(['name'=>'glitchless','title'=>'Glitchless%','description'=>'No glitches or exploits allowed, play the game how God intended.','url'=>url('img/american_dad_speedrun_logo.png')]);
        Category::firstOrCreate(['name'=>'macfarlane','title'=>'MacFarlane%','description'=>'The entire MacFarlane anthology back to back.','url'=>url('img/american_dad_speedrun_logo.png')]);
        Category::firstOrCreate(['name'=>'tas','title'=>'TAS','description'=>'Tool-Assisted Speedruns: first they came for the jobs, now they\'re after the world record.','url'=>url('img/american_dad_speedrun_logo.png')]);

        Platform::firstOrCreate(['name'=>'dos','title'=>'DOS', 'description'=>'Development Build, never intended to be used for anything other than testing until the source code was leaked online mid-development. People have been practicing their runs on this buggy version since.']);
        Platform::firstOrCreate(['name'=>'pc','title'=>'PC', 'description'=>'Port of Gamecube Release, most popular yet controversial speedrun category for its precision and moddability.']);
        Platform::firstOrCreate(['name'=>'gzdad','title'=>'SourcePort: GZDad', 'description'=>'One of the two most popular SourcePorts of the PC release, improves stability and supports 64-bit Operating Systems.']);
        Platform::firstOrCreate(['name'=>'dadspasm','title'=>'SourcePort: DadSpasm', 'description'=>'Second most popular SourcePort, improves stability and allows for easy modification by devs and modders.']);
        Platform::firstOrCreate(['name'=>'gamecube','title'=>'Gamecube', 'description'=>'Original Release, considered universally to be the most stable release. This version is also the most canonically accurate.']);
        Platform::firstOrCreate(['name'=>'snes','title'=>'SNES', 'description'=>'Port of Gamecube release, backwards compatible due to the game releasing between console generations.']);
        Platform::firstOrCreate(['name'=>'ps2','title'=>'Playstation 2', 'description'=>'PS2 Port of XBox release.']);
        Platform::firstOrCreate(['name'=>'xbox','title'=>'XBox', 'description'=>'This platform was very popular at launch, but due to instability and framerate issues, this version performs poorly in speedruns.']);
        Platform::firstOrCreate(['name'=>'wii','title'=>'Wii', 'description'=>'Port of Gamecube Release.']);
        Platform::firstOrCreate(['name'=>'n64','title'=>'Nintendo 64', 'description'=>'Original Release, backwards compatible due to the game releasing between console generations.']);
        Platform::firstOrCreate(['name'=>'smartfridge','title'=>'Smart Fridge', 'description'=>'People say American Dad can run on anything, and this platform proves it. The SourcePort DadSpasm has been heavily modded to support a smart fridge\'s hardware.']);
        Platform::firstOrCreate(['name'=>'3ds','title'=>'3DS', 'description'=>'For the game\'s 5 year anniversary, the devs ported it to the 3DS to be enjoyed on the go.']);
        Platform::firstOrCreate(['name'=>'gameandwatch','title'=>'Game and Watch', 'description'=>'Primitive precursor to the 2005 release.']);

    }
}
