<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Platform;
use App\Models\Speedrun;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SpeedrunTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic feature test example.
     *
     * @return void
     */

    public $speedrun;
    public $category;
    public $platform;

    public function createRun()
    {
        $user = User::factory()->create();
        $platform = Platform::firstOrCreate(['name'=>'xbox'], ['title'=>'Xbox', 'name'=>'xbox', 'url'=>'']);
        $category = Category::firstOrCreate(['name'=>'any'], ['title'=>'Any%', 'name'=>'any', 'url'=>'']);
        $speedrun = new Speedrun(['time'=>'0.5', 'user_id'=>$user->id, 'url'=>'']);
        $speedrun->save();
        $speedrun->platforms()->sync($platform);
        $speedrun->categories()->sync($category);
        $this->speedrun = $speedrun;
        $this->category = $category;
        $this->platform = $platform;
    }

    /** @test */
    public function a_speedrun_has_a_category()
    {
        $this->createRun();
        $this->assertEquals($this->speedrun->category()->id, $this->category->id);
    }
    /** @test */
    public function a_speedrun_has_a_platform()
    {
        $this->createRun();
        $this->assertEquals($this->speedrun->platform()->id, $this->platform->id);
    }
}
