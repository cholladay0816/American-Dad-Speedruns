<?php
if(getenv('APP_ENV')!=='testing'||getenv('DB_DATABASE')!=='/fixture/populated.sqlite')exit(2);
foreach (['cache/data','sessions','views'] as $dir) { if (!is_dir('/app/storage/framework/'.$dir)) mkdir('/app/storage/framework/'.$dir,0777,true); }
require '/app/vendor/autoload.php';$app=require '/app/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Mail::fake();Illuminate\Support\Facades\Notification::fake();Illuminate\Support\Facades\Http::fake();
use Illuminate\Support\Facades\DB;
$mode=getenv('ADSR_REHEARSAL');
if($mode==='legacy'){
 Illuminate\Support\Facades\Artisan::call('migrate',['--force'=>true]);
 $u=App\Models\User::factory()->create(['name'=>'SyntheticHistory','email'=>'history@example.test']);
 $c=App\Models\Category::create(['name'=>'synthetic','title'=>'Synthetic','description'=>'Test only','url'=>'/trophy.svg']);$p=App\Models\Platform::create(['name'=>'synthetic','title'=>'Synthetic','description'=>'Test only']);
 $r=new App\Models\Speedrun(['url'=>'https://youtu.be/HISTORY0001','time'=>'12.34567']);$r->user_id=$u->id;$r->save();$r->categories()->attach($c->id);$r->platforms()->attach($p->id);
 App\Models\Comment::create(['user_id'=>$u->id,'speedrun_id'=>$r->id,'message'=>'Synthetic history retained']);
 $e=App\Models\Election::create(['speedrun_id'=>$r->id,'expiration'=>now()->subDay(),'active'=>1]);
 DB::table('votes')->insert(['election_id'=>$e->id,'user_id'=>$u->id,'positive'=>1,'comment'=>'Synthetic historical vote']);
 $sub=DB::table('subscriptions')->insertGetId(['user_id'=>$u->id,'name'=>'historical','stripe_id'=>'synthetic_subscription','stripe_status'=>'canceled','stripe_plan'=>'synthetic_plan','quantity'=>1]);
 DB::table('subscription_items')->insert(['subscription_id'=>$sub,'stripe_id'=>'synthetic_item','stripe_plan'=>'synthetic_plan','quantity'=>1]);
 DB::table('users')->where('id',$u->id)->update(['two_factor_secret'=>'synthetic-opaque-history','two_factor_recovery_codes'=>'synthetic-opaque-recovery']);
}else if($mode==='forward'){
 Illuminate\Support\Facades\Artisan::call('migrate',['--force'=>true]);
 // Retirement must not mutate existing elections even when this legacy command is invoked.
 Illuminate\Support\Facades\Artisan::call('election:expire');
}else{exit(2);}
$tables=DB::select("SELECT name,sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");$snapshot=[];
foreach($tables as $table){$name=$table->name;if(!preg_match('/^[a-z_]+$/',$name))throw new RuntimeException('Unexpected fixture table');$rows=DB::select('SELECT * FROM "'.$name.'" ORDER BY rowid');$snapshot[$name]=['count'=>count($rows),'sha256'=>hash('sha256',json_encode($rows)),'schema'=>hash('sha256',$table->sql)];}
$result=['mode'=>$mode,'php'=>PHP_VERSION,'framework'=>app()->version(),'tables'=>$snapshot,'integrity'=>DB::select('PRAGMA integrity_check')[0]->integrity_check,'foreignKeyViolations'=>count(DB::select('PRAGMA foreign_key_check'))];
if($mode==='legacy'){file_put_contents('/fixture/legacy-manifest.json',json_encode($result,JSON_PRETTY_PRINT));}
else{$before=json_decode(file_get_contents('/fixture/legacy-manifest.json'),true);$differences=[];foreach($before['tables'] as $name=>$data){if(($snapshot[$name]??null)!==$data)$differences[]=$name;}$result['changedLegacyTables']=$differences;$result['passed']=!$differences&&$result['integrity']==='ok'&&$result['foreignKeyViolations']===0;file_put_contents('/fixture/forward-manifest.json',json_encode($result,JSON_PRETTY_PRINT));echo json_encode(['passed'=>$result['passed'],'tablesCompared'=>count($before['tables']),'changedLegacyTables'=>$differences,'integrity'=>$result['integrity'],'foreignKeyViolations'=>$result['foreignKeyViolations']],JSON_PRETTY_PRINT),"\n";exit($result['passed']?0:1);}
echo 'Populated synthetic legacy database and digest manifest created.',PHP_EOL;
