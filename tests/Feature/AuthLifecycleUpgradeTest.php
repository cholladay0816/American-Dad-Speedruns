<?php
namespace Tests\Feature;
use App\Models\{User,Suspension};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Mail,Notification,Http,Hash,Password,Artisan};
use Illuminate\Console\Scheduling\Schedule;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
class AuthLifecycleUpgradeTest extends TestCase {
 use RefreshDatabase;
 protected function setUp():void{parent::setUp();Mail::fake();Notification::fake();Http::preventStrayRequests();}
 #[Test] public function profile_email_change_preserves_disabled_verification_and_photo_ui():void{
  $u=User::factory()->create(['email_verified_at'=>null]);$this->actingAs($u);
  (new \App\Actions\Fortify\UpdateUserProfileInformation())->update($u,['name'=>'SyntheticEdited','email'=>'edited@example.test']);
  $this->assertSame('edited@example.test',$u->fresh()->email);$this->assertNull($u->fresh()->email_verified_at);Notification::assertNothingSent();
  $this->assertFalse(\Laravel\Jetstream\Jetstream::managesProfilePhotos());$this->get('/user/profile')->assertOk()->assertDontSee('Select A New Photo');
 }
 #[Test] public function valid_reset_token_changes_password_once_and_rejects_reuse():void{
  $u=User::factory()->create(['email'=>'reset@example.test']);$token=Password::broker()->createToken($u);$input=['email'=>$u->email,'token'=>$token,'password'=>'Synthetic-New-Password123!','password_confirmation'=>'Synthetic-New-Password123!'];
  $this->post('/reset-password',$input)->assertSessionHasNoErrors()->assertRedirect();$this->assertTrue(Hash::check($input['password'],$u->fresh()->password));
  $this->post('/reset-password',$input)->assertSessionHasErrors('email');
 }
 #[Test] public function password_change_requires_current_password():void{
  $u=User::factory()->create(['password'=>Hash::make('Synthetic-Old-Password123!')]);$this->actingAs($u);
  $this->putJson('/user/password',['current_password'=>'wrong','password'=>'Synthetic-New-Password123!','password_confirmation'=>'Synthetic-New-Password123!'])->assertUnprocessable()->assertJsonValidationErrors('current_password');
  $this->putJson('/user/password',['current_password'=>'Synthetic-Old-Password123!','password'=>'Synthetic-New-Password123!','password_confirmation'=>'Synthetic-New-Password123!'])->assertSuccessful();$this->assertTrue(Hash::check('Synthetic-New-Password123!',$u->fresh()->password));
 }
 #[Test] public function active_schedule_excludes_retired_jobs_and_expiry_preserves_active_suspensions():void{
  $commands=implode("\n",array_map(fn($e)=>$e->command??'',$this->app->make(Schedule::class)->events()));
  foreach(['speedruns:disqualifybrokenvideos','banner:expire','suspensions:expire'] as $command)$this->assertStringContainsString($command,$commands);
  $this->assertStringNotContainsString('election:expire',$commands);$this->assertStringNotContainsString('council',$commands);
  $u=User::factory()->create();$expired=new Suspension();$expired->user_id=$u->id;$expired->expiration=now()->subDay();$expired->save();$active=new Suspension();$active->user_id=$u->id;$active->expiration=now()->addDay();$active->save();Artisan::call('suspensions:expire');$this->assertModelMissing($expired);$this->assertModelExists($active);
 }
 #[Test] public function account_deletion_requires_password_and_removes_only_synthetic_user():void{
  $u=User::factory()->create(['password'=>Hash::make('Synthetic-Password123!')]);$other=User::factory()->create();$this->actingAs($u);
  \Livewire\Livewire::test(\Laravel\Jetstream\Http\Livewire\DeleteUserForm::class)->set('password','wrong')->call('deleteUser')->assertHasErrors('password');$this->assertModelExists($u);
  \Livewire\Livewire::test(\Laravel\Jetstream\Http\Livewire\DeleteUserForm::class)->set('password','Synthetic-Password123!')->call('deleteUser')->assertRedirect('/');$this->assertModelMissing($u);$this->assertModelExists($other);$this->assertGuest();
 }
}
