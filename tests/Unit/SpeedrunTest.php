<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Platform;
use App\Models\Speedrun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SpeedrunTest extends TestCase
{
    use RefreshDatabase;

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
        $this->platform = Platform::factory()->create(['title'=>'Xbox', 'name'=>'xbox']);
        $this->category = Category::factory()->create(['title'=>'Any%', 'name'=>'any']);
        $this->speedrun = Speedrun::factory()->create(['time'=>'0.5', 'user_id'=>$this->user->id]);
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
    /** @test */
    public function it_has_a_cache_key() {
        $this->createRun();
        $actual = 'App\Models\Speedrun/' . $this->speedrun->id . '-' . $this->speedrun->updated_at->timestamp;
        $this->assertEquals($this->speedrun->getCacheKey(), $actual);
    }

}
