<?php
namespace Tests\Feature;
use App\Models\{User,Category,Platform,Speedrun,Disqualification};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Mail,Http};
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
class RankingValidationUpgradeTest extends TestCase {
 use RefreshDatabase;
 private function fixtures():array{
  Mail::fake();Http::preventStrayRequests();$u=User::factory()->create();$c=Category::factory()->create();$p=Platform::factory()->create();$runs=[];
  foreach(['1.23456','1.23456','1.23457','2.00000'] as $i=>$time){$r=Speedrun::create(['user_id'=>$u->id,'url'=>'https://youtu.be/RANK000000'.$i,'time'=>$time,'verified'=>1]);$r->categories()->attach($c);$r->platforms()->attach($p);$runs[]=$r;}
  return [$u,$c,$p,$runs];
 }
 #[Test] public function competition_ties_and_five_decimal_order_are_preserved():void{
  [$u,$c,$p,$runs]=$this->fixtures();$this->assertSame([1,1,3,4],array_map(fn($r)=>$r->placement(),$runs));
  $runs[2]->disqualify('Synthetic ranking exclusion');$this->assertSame(3,$runs[3]->fresh()->placement());
  $runs[0]->verified=0;$runs[0]->save();$this->assertSame(2,$runs[3]->fresh()->placement());
 }
 #[Test] public function global_order_excludes_unverified_and_moves_disqualified_last():void{
  [$u,$c,$p,$runs]=$this->fixtures();$runs[0]->verified=0;$runs[0]->save();$runs[1]->disqualify('Synthetic ordering');
  $this->get('/speedruns')->assertOk()->assertViewHas('speedruns',fn($rs)=>$rs->pluck('id')->values()->all()===[$runs[2]->id,$runs[3]->id,$runs[1]->id]);
  // Characterize existing category behavior: unverified rows are included, unlike global list.
  $this->get('/categories/'.$c->name)->assertOk()->assertViewHas('speedruns',fn($rs)=>$rs->pluck('id')->values()->all()===[$runs[0]->id,$runs[2]->id,$runs[3]->id,$runs[1]->id]);
 }
 #[Test] public function current_unpaginated_list_does_not_drop_rows_on_page_query():void{
  [$u,$c,$p,$runs]=$this->fixtures();for($i=0;$i<21;$i++){$r=Speedrun::create(['user_id'=>$u->id,'url'=>'https://youtu.be/PAGE'.str_pad($i,7,'0',STR_PAD_LEFT),'time'=>3+$i,'verified'=>1]);$r->categories()->attach($c);$r->platforms()->attach($p);}
  foreach(['/speedruns','/speedruns?page=2'] as $url)$this->get($url)->assertOk()->assertViewHas('speedruns',fn($rs)=>$rs->count()===25);
 }
 #[Test] public function invalid_submission_is_422_without_partial_writes_or_mail():void{
  [$u,$c,$p,$runs]=$this->fixtures();$this->actingAs($u);$valid=['url'=>'https://youtu.be/VALID000001','time'=>'0.00001','category'=>$c->id,'platform'=>$p->id];
  foreach([['time',0],['time',-1],['time','not-a-number'],['category',99999],['platform',99999],['url','https://example.test/video'],['url','https://youtu.be/'.str_repeat('x',20)],['url',$runs[0]->url]] as [$field,$value]){$this->postJson('/speedruns/new',array_replace($valid,[$field=>$value]))->assertUnprocessable()->assertJsonValidationErrors($field);$this->assertDatabaseCount('speedruns',4);}
  Mail::assertNothingQueued();$this->post('/speedruns/new',$valid)->assertSessionHasNoErrors()->assertRedirect();$this->assertDatabaseCount('speedruns',5);
 }
 #[Test] public function missing_run_category_platform_and_watch_fail_without_server_error():void{
  foreach(['/speedruns/999999','/watch/999999','/categories/missing-synthetic','/platforms/missing-synthetic'] as $path)$this->get($path)->assertNotFound();
 }
}
