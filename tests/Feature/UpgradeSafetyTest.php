<?php
namespace Tests\Feature;
use App\Models\{User,Speedrun,Comment,Role,Ability,Category,Platform,Election,Suspension};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Auth,Mail,Notification,Schema,Artisan};
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
class UpgradeSafetyTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); Mail::fake(); Notification::fake(); }
    private function runFor(User $owner): Speedrun {
        $r=new Speedrun(['url'=>'https://youtu.be/TEST0000001','time'=>'12.3456']);$r->user_id=$owner->id;$r->save();
        $c=Category::create(['name'=>'synthetic','title'=>'Synthetic','description'=>'Test only','url'=>'/trophy.svg']);$p=Platform::create(['name'=>'synthetic','title'=>'Synthetic','description'=>'Test only']);$r->categories()->attach($c->id);$r->platforms()->attach($p->id);return $r;
    }
    private function moderator(User $u): void {
        $role=Role::create(['name'=>'synthetic-moderator','title'=>'Synthetic moderator']);
        foreach(['view_admin','manage_speedruns'] as $name){$a=Ability::create(['name'=>$name,'title'=>$name]);$role->abilities()->attach($a->id);}$u->roles()->attach($role->id);
    }
    #[Test]
    public function comment_deletion_respects_author_owner_moderator_and_suspension(): void {
        $owner=User::factory()->create();$author=User::factory()->create();$other=User::factory()->create();$mod=User::factory()->create();$this->moderator($mod);$run=$this->runFor($owner);
        $comment=Comment::create(['user_id'=>$author->id,'speedrun_id'=>$run->id,'message'=>'Synthetic ownership regression']);
        foreach([[$author,true],[$owner,true],[$other,false],[$mod,true]] as [$u,$allowed]){$this->actingAs($u->fresh());$copy=$comment->fresh();$this->assertSame($allowed,$copy->canDelete());$this->assertEquals($owner->id,$copy->speedrun->user_id,'Authorization must not mutate the owner');}
        $this->actingAs($other);$component=new \App\Http\Livewire\Comments();$component->speedrun=$run;$component->delete($comment->id);$this->assertModelExists($comment);
        $s=new Suspension();$s->user_id=$author->id;$s->save();$this->actingAs($author->fresh());$this->assertFalse($comment->fresh()->canDelete());
        Auth::logout();$this->assertFalse($comment->fresh()->canDelete());
    }
    #[Test]
    public function retired_routes_and_livewire_actions_are_unavailable(): void {
        foreach(['/billing-portal','/council','/council/join','/elections','/elections/1','/stripe/payment/example'] as $path)$this->get($path)->assertNotFound();
        $this->post('/stripe/webhook',[])->assertNotFound();
        foreach([new \App\Http\Livewire\UpdateBillingInformation(),new \App\Http\Livewire\ElectionWatcher(),new \App\Http\Livewire\CouncilChatroom()] as $component){try{$component->render();$this->fail('Retired component rendered');}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(410,$e->getStatusCode());}}
    }
    #[Test]
    public function verification_does_not_create_elections_and_retired_expiry_preserves_history(): void {
        $u=User::factory()->create();$this->moderator($u);$run=$this->runFor($u);$history=Election::create(['speedrun_id'=>$run->id,'expiration'=>now()->subDay(),'active'=>1]);
        $this->actingAs($u->fresh())->patch('/speedruns/'.$run->id)->assertRedirect();$this->assertEquals(1,$run->fresh()->verified);$this->assertSame(1,Election::count());
        Artisan::call('election:expire');$this->assertModelExists($history);$this->assertEquals(1,$history->fresh()->active);Mail::assertNotQueued(\App\Mail\ElectionStarted::class);
        $this->assertTrue(Schema::hasTable('subscriptions'));$this->assertTrue(Schema::hasTable('votes'));
    }
    #[Test]
    public function observed_live_auth_behavior_is_preserved_without_enrollment_routes(): void {
        $u=User::factory()->create(['email_verified_at'=>null]);$this->assertNotInstanceOf(\Illuminate\Contracts\Auth\MustVerifyEmail::class,$u);$this->assertFalse(in_array(\Laravel\Fortify\TwoFactorAuthenticatable::class,class_uses_recursive($u)));
        $this->actingAs($u)->get('/speedruns/new')->assertOk();$this->post('/user/two-factor-authentication')->assertNotFound();
        $this->assertTrue(Schema::hasColumn('users','two_factor_secret'));
    }
}
