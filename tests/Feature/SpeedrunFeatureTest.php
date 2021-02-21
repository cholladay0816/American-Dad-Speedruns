<?php

namespace Tests\Feature;

use App\Http\Livewire\SpeedrunTable;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Platform;
use App\Models\Role;
use App\Models\Speedrun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class SpeedrunFeatureTest extends TestCase
{
    use RefreshDatabase;

    //Generates a new user with a verified email
    public function getUser()
    {
        return User::factory()->create(['email_verified_at'=>now()]);
    }

    /** @test */
    public function guests_cannot_create_or_store_speedruns()
    {
        // (As a guest) attempt to visit the create speedrun route
        $response = $this->get('/speedruns/new');
        // Assert the user was redirected due to being unauthorized
        $response->assertStatus(302);

        // (As a guest) submit a post to the speedrun post route
        $response = $this->post('/speedruns/new',
            ['url'=>'https://youtu.be/a', 'time'=>0.1, 'category'=>Category::factory()->create()->id, 'platform'=>Platform::factory()->create()->id]);
        // Assert the user was redirected due to being unauthorized
        $response->assertStatus(302);

    }
    /** @test */
    public function a_logged_in_verified_user_can_view_create_speedruns()
    {
        // Create and login as a user
        $this->actingAs($this->getUser());

        // Try to visit the new speedrun route
        $response = $this->get('/speedruns/new');
        // Assert that the user visits the page successfully
        $response->assertStatus(200);
    }
    /** @test */
    public function a_speedrun_must_have_a_working_url()
    {
        // Create and login as a user
        $this->actingAs($this->getUser());

        // Try to post an empty link
        $response = $this->post('/speedruns/new',
            ['url'=>'', 'time'=>0.1, 'category'=>Category::factory()->create()->id, 'platform'=>Platform::factory()->create()->id]);

        // Assert that the controller failed to validate
        $response->assertSessionHasErrors('url');

        // Try to post an invalid link
        $response = $this->post('/speedruns/new',
            ['url'=>'a', 'time'=>0.1, 'category'=>Category::factory()->create()->id, 'platform'=>Platform::factory()->create()->id]);

        // Assert the controller failed to validate
        $response->assertSessionHasErrors('url');
    }

    /** @test */
    public function a_speedrun_can_be_created()
    {
        // Create and login as a user
        $user = $this->getUser();
        $this->actingAs($user);

        // Create a new speedrun
        $response = $this->post('/speedruns/new',
            ['url'=>'https://youtu.be/a', 'time'=>0.1, 'category'=>Category::factory()->create()->id, 'platform'=>Platform::factory()->create()->id]);
        // Assert the post was successful
        $response->assertSessionHas('success');
        // Assert we go back to the user's run page
        $response->assertRedirect('/runner/' . $user->name);
    }

    /** @test */
    public function a_speedrun_can_be_viewed()
    {
        // Create and login as a user
        $user = $this->getUser();
        $this->actingAs($user);

        // Create a speedrun
        $response = $this->post('/speedruns/new',
            ['url'=>'https://youtu.be/a', 'time'=>0.1, 'category'=>Category::factory()->create()->id, 'platform'=>Platform::factory()->create()->id]);
        $run = Speedrun::first();

        // Assert the run exists and does not 404
        $response = $this->get('/watch/' . $run->id);
        $response->assertOk();
    }

    /** @test */
    public function a_speedrun_can_be_disqualified()
    {
        // Create and login as user
        $user = $this->getUser();
        $this->actingAs($user);

        // Populate database with admin roles
        $this->seed();

        // Set user to admin
        $user->roles()->sync([Role::where('name', 'administrator')->first()->id]);

        // Create a speedrun
        $this->post('/speedruns/new',
            ['url'=>'https://youtu.be/a', 'time'=>0.1, 'category'=>Category::factory()->create()->id, 'platform'=>Platform::factory()->create()->id]);
        $run = Speedrun::first();

        // Disqualify the run
        $this->post(route('disqualify.store', ['speedrun'=>$run]), ['reason'=>'Test Disqualification']);

        // Assert that the run is now disqualified for visitors
        $response = $this->get('/watch/' . $run->id);
        $response->assertSeeText('Test Disqualification');
    }

    /** @test */
    public function a_speedrun_can_be_removed()
    {
        // Create and login as a user
        $user = $this->getUser();
        $this->actingAs($user);

        // Submit a new speedrun
        $response = $this->post('/speedruns/new',
            ['url'=>'https://youtu.be/a', 'time'=>0.1, 'category'=>Category::factory()->create()->id, 'platform'=>Platform::factory()->create()->id]);
        $speedrun = $user->fresh()->speedruns->first();

        // Delete the speedrun using the delete run
        $this->delete('/speedruns/' . $speedrun->id);

        // Assert that when the watch page is visited, a 404 appears, meaning the run was deleted successfully.
        $response = $this->get('/watch/' . $speedrun->id);
        $response->assertNotFound();

    }

    // TODO: Undo DQ, and Remove (Admin)


    /** @test */
    public function it_can_generate_a_cache() {
        $this->seed();
        $speedrun = Speedrun::factory()->create();
        $speedrun->categories()->attach(Category::first());
        $speedrun->platforms()->attach(Platform::first());

        Livewire::test(SpeedrunTable::class)
            ->set('speedruns', [$speedrun])
            ->call('loadRuns')
            ->assertSee($speedrun->user->name);

        $this->assertTrue(Cache::has($speedrun->getCacheKey()));

    }

    /** @test */
    public function it_can_revoke_a_cache_on_update() {
        $this->seed();
        $speedrun = Speedrun::factory()->create();
        $speedrun->categories()->attach(Category::first());
        $speedrun->platforms()->attach(Platform::first());

        Livewire::test(SpeedrunTable::class)
            ->set('speedruns', [$speedrun])
            ->call('loadRuns')
            ->assertSee($speedrun->user->name);

        $oldKey = $speedrun->getCacheKey();

        $this->assertTrue(Cache::has($speedrun->getCacheKey()));

        $speedrun->time = 5;
        $speedrun->save();

        $newKey = $speedrun->getCacheKey();

        $this->assertFalse(Cache::has($oldKey));

        Livewire::test(SpeedrunTable::class)
            ->set('speedruns', [$speedrun])
            ->call('loadRuns')
            ->assertSee($speedrun->user->name);

        $this->assertTrue(Cache::has($newKey));

    }

}
