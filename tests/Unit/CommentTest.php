<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Platform;
use App\Models\Speedrun;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    use DatabaseTransactions;
    public $speedrun;
    public $category;
    public $platform;
    public function createSpeedruns()
    {
        $user = User::factory()->create();
        $platform = Platform::firstOrCreate(['name'=>'xbox'], ['title'=>'Xbox', 'name'=>'xbox', 'url'=>'']);
        $category = Category::firstOrCreate(['name'=>'any'], ['title'=>'Any%', 'name'=>'any', 'url'=>'']);
        $speedrun = new Speedrun(['time'=>'0.5', 'user_id'=>$user->id, 'url'=>'']);
        $speedrun->save();
        $speedrun->platform()->sync($platform);
        $speedrun->category()->sync($category);
    }

    /** test */
    public function a_user_can_comment_on_a_speedrun()
    {
        $user = User::factory()->create();

        $comment = Comment::factory()->create(['user_id'=>$user->id]);

        self::assertContains($comment, $this->speedrun->fresh()->comments);

    }
}
