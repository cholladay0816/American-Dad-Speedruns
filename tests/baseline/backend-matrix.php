<?php
if(getenv('APP_ENV')!=='testing'||getenv('DB_DATABASE')!==':memory:')exit(2);
require '/app/vendor/autoload.php';$app=require '/app/bootstrap/app.php';$kernel=$app->make(Illuminate\Contracts\Console\Kernel::class);$kernel->bootstrap();
Illuminate\Support\Facades\Mail::fake();Illuminate\Support\Facades\Http::fake();Illuminate\Support\Facades\Artisan::call('migrate',['--force'=>true]);
$checks=[];$check=function($name,$pass)use(&$checks){$checks[]=['check'=>$name,'passed'=>(bool)$pass];};
$abilities=['view_admin','manage_speedruns','manage_banners'];
foreach($abilities as $ability){App\Models\Ability::create(['name'=>$ability,'title'=>$ability]);}
foreach(['ordinary'=>[], 'view-only'=>['view_admin'],'runs-only'=>['manage_speedruns'],'banners-only'=>['manage_banners'],'moderator'=>['view_admin','manage_speedruns'],'administrator'=>$abilities] as $name=>$grants){
 $u=App\Models\User::factory()->create(['email'=>$name.'@example.test']);$r=App\Models\Role::create(['name'=>$name,'title'=>$name]);$u->roles()->attach($r->id);$r->abilities()->attach(App\Models\Ability::whereIn('name',$grants)->pluck('id'));
 foreach($abilities as $ability)$check($name.': '.$ability,Illuminate\Support\Facades\Gate::forUser($u)->allows($ability)===in_array($ability,$grants));
}
$u=App\Models\User::factory()->create(['email'=>'suspended@example.test']);$s=new App\Models\Suspension();$s->user_id=$u->id;$s->save();$check('suspended user denied use_site gate',Illuminate\Support\Facades\Gate::forUser($u)->denies('use_site'));
$check('synthetic queue is synchronous',config('queue.default')==='sync');
$schedule=$app->make(Illuminate\Console\Scheduling\Schedule::class);$commands=array_map(fn($e)=>['command'=>$e->command,'expression'=>$e->expression],$schedule->events());
$check('retired schedules absent',count(array_filter($commands,fn($e)=>preg_match('/(?:council|election):expire/',$e['command'])))===0);
echo json_encode(['scope'=>'gate matrix and scheduler registration; not full HTTP authorization or scheduled execution','checks'=>$checks,'schedules'=>$commands],JSON_PRETTY_PRINT),"\n";exit(count(array_filter($checks,fn($c)=>!$c['passed']))?1:0);
