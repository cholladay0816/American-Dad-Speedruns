<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ObsoleteSignupBannerTest extends TestCase
{
    use RefreshDatabase;

    private const OBSOLETE_TITLE = 'Click here to sign up, the top bar is currently broken.';

    private function notice(User $owner, string $title, int $daysOld): Banner
    {
        return Banner::create([
            'user_id' => $owner->id,
            'title' => $title,
            'url' => url('/register'),
            'bcolor' => 'blue-600',
            'tcolor' => 'white',
            'expiration' => now()->addWeek(),
            'created_at' => now()->subDays($daysOld),
        ]);
    }

    #[Test]
    public function obsolete_notice_is_skipped_without_hiding_other_banners_or_registration(): void
    {
        $owner = User::factory()->create();
        $obsolete = $this->notice($owner, self::OBSOLETE_TITLE, 2);
        $current = $this->notice($owner, 'Synthetic current announcement', 1);
        $owner->banners()->attach([$obsolete->id, $current->id]);

        $this->get('/')->assertOk()
            ->assertViewHas('banner', fn ($banner) => $banner->is($current))
            ->assertSeeText($current->title)
            ->assertSee('href="'.url('/register').'"', false)
            ->assertDontSeeText(self::OBSOLETE_TITLE);
        $this->get('/register')->assertOk();

        $this->actingAs($owner)->get('/dashboard')->assertOk()
            ->assertViewHas('banner', fn ($banner) => $banner->is($current))
            ->assertSeeText($current->title)
            ->assertDontSeeText(self::OBSOLETE_TITLE);

        $this->assertModelExists($obsolete);
        $this->assertDatabaseCount('banners', 2);
    }

    #[Test]
    public function obsolete_notice_alone_renders_no_homepage_banner_for_guests_or_members(): void
    {
        $owner = User::factory()->create();
        $obsolete = $this->notice($owner, self::OBSOLETE_TITLE, 1);
        $owner->banners()->attach($obsolete);

        $this->get('/')->assertOk()->assertViewHas('banner', fn ($banner) => $banner === null)
            ->assertDontSeeText(self::OBSOLETE_TITLE);
        $this->actingAs($owner)->get('/dashboard')->assertOk()
            ->assertViewHas('banner', fn ($banner) => $banner === null)->assertDontSeeText(self::OBSOLETE_TITLE);

        $this->assertModelExists($obsolete);
    }
}
