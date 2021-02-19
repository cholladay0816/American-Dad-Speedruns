<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Platform;
use App\Models\Speedrun;
use Illuminate\Database\Seeder;

class SpeedrunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $speedruns = Speedrun::factory(1000)->create(['verified' => 1]);
        foreach ($speedruns as $speedrun) {
            $speedrun->categories()->attach(Category::inRandomOrder()->first());
            $speedrun->platforms()->attach(Platform::inRandomOrder()->first());
        }
    }
}
