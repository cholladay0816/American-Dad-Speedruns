<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Platform;
use App\Models\Speedrun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public $user;
    public $speedrun;
    public $category;
    public $platform;

    public function createSpeedruns()
    {
        $this->user = User::factory()->create();
        $this->platform = Platform::firstOrCreate(['title'=>'Xbox'], ['title'=>'Xbox', 'name'=>'xbox', 'url'=>'']);
        $this->category = Category::firstOrCreate(['title'=>'Any%'], ['title'=>'Any%', 'name'=>'any', 'url'=>'']);
        $this->speedrun = new Speedrun(['time'=>'0.5', 'user_id'=>$this->user->id, 'url'=>'']);
        $this->speedrun->save();
        $this->speedrun->platforms()->sync([$this->platform->id]);
        $this->speedrun->categories()->sync([$this->category->id]);
    }

    /** @test */
    public function a_user_can_comment_on_a_speedrun()
    {
        $this->createSpeedruns();

        $comment = Comment::factory()->create(['user_id'=>$this->user->id, 'speedrun_id'=>$this->speedrun->id]);

        $this->assertContains($comment->message, $this->speedrun->fresh()->comments->pluck('message'));

    }
}
