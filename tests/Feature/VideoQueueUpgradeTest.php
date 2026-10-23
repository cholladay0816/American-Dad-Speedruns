<?php
namespace Tests\Feature;
use App\Models\{User,Speedrun,Category,Platform};
use App\Mail\DisqualifiedRun;
use App\Services\VideoAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Http,Mail,Artisan};
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
class VideoQueueUpgradeTest extends TestCase {
 use RefreshDatabase;
 private function runFixture(string $id='TEST0000001'):Speedrun {
  $u=User::factory()->create(['email'=>$id.'@example.test']);$c=Category::factory()->create();$p=Platform::factory()->create();
  $r=Speedrun::create(['user_id'=>$u->id,'url'=>'https://youtu.be/'.$id,'time'=>'12.34567','verified'=>1]);$r->categories()->attach($c);$r->platforms()->attach($p);return $r;
 }
 private function fake($callback):void{Http::swap(new \Illuminate\Http\Client\Factory());Http::preventStrayRequests();Http::fake($callback);}
 #[Test] public function mixed_batch_classifies_private_inaccessible_and_missing_without_harming_available_video():void{
  Mail::fake();$good=$this->runFixture('HEALTHY0001');$bad=[];
  foreach([401=>'Unauthorized',403=>'Forbidden',404=>'Not Found',410=>'Gone'] as $status=>$body)$bad[$status]=$this->runFixture('VIDEO'.$status.'001');
  $this->fake(function($request){foreach([401=>'Unauthorized',403=>'Forbidden',404=>'Not Found',410=>'Gone'] as $status=>$body)if(str_contains($request->url(),'VIDEO'.$status.'001'))return Http::response($body,$status,['Content-Type'=>'text/plain']);return Http::response(['type'=>'video','provider_name'=>'YouTube'],200);});
  Artisan::call('speedruns:disqualifybrokenvideos');$this->assertFalse($good->fresh()->disqualified());
  foreach($bad as $status=>$run){$dq=$run->fresh()->disqualification;$this->assertNotNull($dq);$this->assertStringContainsString('HTTP '.$status,$dq->reason);$this->assertStringContainsString($status<404?'private or inaccessible':'missing or unavailable',$dq->reason);$this->assertStringContainsString('not confirmed',$dq->reason);$this->assertStringContainsString($run->video_id(),$dq->evidence);}
  Artisan::call('speedruns:disqualifybrokenvideos');$this->assertDatabaseCount('disqualifications',4);Mail::assertQueued(DisqualifiedRun::class,4);
 }
 #[Test] public function provider_wide_auth_quota_and_transient_failures_leave_every_run_unchanged():void{
  Mail::fake();$a=$this->runFixture();$b=$this->runFixture('HEALTHY0001');
  foreach([401,403,404,410,429,503] as $status){$this->fake(fn()=>Http::response([401=>'Unauthorized',403=>'Forbidden',404=>'Not Found',410=>'Gone'][$status]??'Unavailable',$status,['Content-Type'=>'text/plain']));Artisan::call('speedruns:disqualifybrokenvideos');$this->assertDatabaseCount('disqualifications',0);}
  $this->fake(fn($r)=>str_contains($r->url(),'HEALTHY0001')?Http::response(['type'=>'video','provider_name'=>'YouTube'],200):Http::response(['error'=>['reason'=>'quotaExceeded']],403));Artisan::call('speedruns:disqualifybrokenvideos');$this->assertDatabaseCount('disqualifications',0);
  $this->fake(fn()=>Http::failedConnection());Artisan::call('speedruns:disqualifybrokenvideos');$this->assertDatabaseCount('disqualifications',0);Mail::assertNothingQueued();
 }
 #[Test] public function ambiguous_challenges_and_unrecognized_responses_are_not_video_evidence():void{
  $checker=new VideoAvailability();foreach([401,403,404,410] as $status){$this->fake(fn()=>Http::response('<html>Access challenge</html>',$status,['Content-Type'=>'text/html']));$this->assertSame('unknown',$checker->inspect('TEST0000001')['state']);}
  $this->fake(fn()=>Http::response('Forbidden',403,['Content-Type'=>'text/plain']));$result=$checker->inspect('TEST0000001');$this->assertSame('Forbidden',$result['body']);$this->assertSame(hash('sha256','Forbidden'),$result['body_sha256']);$this->assertSame(403,$result['status']);
 }
 #[Test] public function database_queue_worker_renders_synthetic_mail_to_array_transport_only():void {
  Http::preventStrayRequests();config(['queue.default'=>'database','mail.default'=>'array','mail.from.address'=>'synthetic@example.test','mail.from.name'=>'Synthetic']);$r=$this->runFixture();$r->disqualify('Synthetic worker rehearsal');$this->assertDatabaseCount('jobs',1);
  Artisan::call('queue:work',['connection'=>'database','--once'=>true,'--tries'=>1,'--timeout'=>10]);$this->assertDatabaseCount('jobs',0);$this->assertDatabaseCount('failed_jobs',0);$this->assertCount(1,Mail::mailer('array')->getSymfonyTransport()->messages());
 }
}
