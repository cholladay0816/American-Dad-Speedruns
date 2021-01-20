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
    public $user;
    public $speedrun;
    public $category;
    public $platform;

    public function createRun()
    {
        $this->user = User::factory()->create();
        $this->platform = Platform::firstOrCreate(['name'=>'xbox'], ['title'=>'Xbox', 'name'=>'xbox', 'url'=>'']);
        $this->category = Category::firstOrCreate(['name'=>'any'], ['title'=>'Any%', 'name'=>'any', 'url'=>'']);
        $this->speedrun = new Speedrun(['time'=>'0.5', 'user_id'=>$this->user->id, 'url'=>'']);
        $this->speedrun->save();
        $this->speedrun->platforms()->sync($this->platform);
        $this->speedrun->categories()->sync($this->category);

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
    /** @test */
    public function a_speedrun_has_an_owner()
    {
        $this->createRun();
        $this->assertEquals($this->speedrun->user->id, $this->user->id);
    }
}
