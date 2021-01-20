<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Platform;
use App\Models\Speedrun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpeedrunFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function getUser()
    {
        return User::factory()->create(['email_verified_at'=>now()]);
    }

    /** @test */
    public function guests_cannot_view_or_create_speedruns()
    {
        $response = $this->get('/speedruns/new');
        $response->assertStatus(302);
        $response = $this->post('/speedruns/new',
            ['url'=>'https://youtu.be/a', 'time'=>0.1, 'category'=>Category::factory()->create()->id, 'platform'=>Platform::factory()->create()->id]);
        $response->assertStatus(302);

    }
    /** @test */
    public function a_logged_in_verified_user_can_view_create_speedruns()
    {
        $this->actingAs($this->getUser());

        $response = $this->get('/speedruns/new');
        $response->assertStatus(200);
    }
    /** @test */
    public function a_speedrun_must_have_a_working_url()
    {
        $this->actingAs($this->getUser());

        $response = $this->post('/speedruns/new',
            ['url'=>'', 'time'=>0.1, 'category'=>Category::factory()->create()->id, 'platform'=>Platform::factory()->create()->id]);
        $response->assertSessionHasErrors('url');
        $response = $this->post('/speedruns/new',
            ['url'=>'a', 'time'=>0.1, 'category'=>Category::factory()->create()->id, 'platform'=>Platform::factory()->create()->id]);
        $response->assertSessionHasErrors('url');
    }

    /** @test */
    public function a_speedrun_can_be_created()
    {
        $user = $this->getUser();
        $this->actingAs($user);

        $response = $this->post('/speedruns/new',
            ['url'=>'https://youtu.be/a', 'time'=>0.1, 'category'=>Category::factory()->create()->id, 'platform'=>Platform::factory()->create()->id]);
        $response->assertRedirect('/runner/' . $user->name);
        $response->assertSessionHas('success');
    }

    /** @test */
    public function a_speedrun_can_be_viewed()
    {
        $user = $this->getUser();
        $this->actingAs($user);
        $response = $this->post('/speedruns/new',
            ['url'=>'https://youtu.be/a', 'time'=>0.1, 'category'=>Category::factory()->create()->id, 'platform'=>Platform::factory()->create()->id]);
        $run = Speedrun::first();

        $response = $this->get('/watch/' . $run->id);
        $response->assertSeeText($user->name);
    }

    /** @test */
    public function a_speedrun_can_be_removed()
    {
        $user = $this->getUser();
        $this->actingAs($user);
        $response = $this->post('/speedruns/new',
            ['url'=>'https://youtu.be/a', 'time'=>0.1, 'category'=>Category::factory()->create()->id, 'platform'=>Platform::factory()->create()->id]);
        $speedrun = $user->fresh()->speedruns->first();

        $this->delete('/speedruns/' . $speedrun->id);

        $response = $this->get('/watch/' . $speedrun->id);

        $response->assertNotFound();

    }
    // Disqualify, Undo, and Remove (Admin)
}
