<?php

namespace Tests\Feature;

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


    public function testSpeedruns()
    {
        # Logged in
        $this->actingAs(User::first());

        $response = $this->get('/speedruns/new');
        $response->assertStatus(200);

        $response = $this->post('/speedruns/new',
            ['url'=>'', 'time'=>0.1, 'category'=>Category::first()->id, 'platform'=>Platform::first()->id]);
        $response->assertSessionHasErrors();

        $response = $this->post('/speedruns/new',
            ['url'=>'https://youtu.be/a', 'time'=>0.1, 'category'=>Category::first()->id, 'platform'=>Platform::first()->id]);
        $response->assertRedirect('/runner/' . User::first()->name);
        $response->assertSessionHas('success');

        $last = Speedrun::orderBy('created_at', 'DESC')->first();

        $response = $this->patch('/speedruns/' . $last->id);
        $response->assertSessionHas('success');

        $response = $this->get('admin/disqualify/' . $last->id);
        $response->assertOk();

        $response = $this->post('admin/disqualify/' . $last->id, ['reason'=>'Testing']);
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $response = $this->delete('/speedruns/' . $last->id);
        $response->assertSessionHas('success');

    }
}
