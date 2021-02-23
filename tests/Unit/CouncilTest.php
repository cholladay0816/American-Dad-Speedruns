<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouncilTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_a_user()
    {
        $this->seed();
        $user = User::factory()->create();
        $council = Role::where('name', 'council')->first();
        $user->roles()->attach($council);

        $this->assertContains($user->id, $council->users->pluck('id'));
    }
}
