<?php
$app=require __DIR__.'/bootstrap-browser.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$matrix=['Admin'=>['view_admin','manage_speedruns','manage_banners'],'Moderator'=>['view_admin','manage_speedruns'],'ViewOnly'=>['view_admin'],'RunsOnly'=>['manage_speedruns'],'BannersOnly'=>['manage_banners'],'Suspended'=>[]];
foreach($matrix as $name=>$grants){
 $u=App\Models\User::firstOrCreate(['email'=>strtolower($name).'@example.test'],['name'=>'Synthetic'.$name,'password'=>Illuminate\Support\Facades\Hash::make('Synthetic-only-Password123!')]);
 $role=App\Models\Role::firstOrCreate(['name'=>strtolower($name)],['title'=>$name]);$u->roles()->syncWithoutDetaching([$role->id]);
 foreach($grants as $grant){$a=App\Models\Ability::firstOrCreate(['name'=>$grant],['title'=>$grant]);$role->abilities()->syncWithoutDetaching([$a->id]);}
 if($name==='Suspended'&&!$u->suspensions()->exists()){$s=new App\Models\Suspension();$s->user_id=$u->id;$s->save();}
}
// Empty historical council role satisfies the legacy controller's data invariant; no members/providers.
App\Models\Role::firstOrCreate(['name'=>'council'],['title'=>'Retired historical council']);
echo "Synthetic role fixtures ready.\n";
