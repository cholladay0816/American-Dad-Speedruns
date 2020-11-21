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

        $admin->abilities()->sync($m_users);
        $admin->abilities()->sync($m_runs);
        $admin->abilities()->sync($v_admin);

        $mod->abilities()->sync($m_runs);
        $mod->abilities()->sync($v_admin);
    }
}
