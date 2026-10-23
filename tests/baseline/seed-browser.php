<?php
$app=require __DIR__.'/bootstrap-browser.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Mail::fake();
Illuminate\Support\Facades\Notification::fake();
Illuminate\Support\Facades\Http::fake();
Illuminate\Support\Facades\Artisan::call('migrate',['--force'=>true]);
if (App\Models\User::where('email','runner@example.test')->exists()) { echo "Synthetic fixture already present.\n"; exit(0); }
foreach(['Runner','Unverified','Other'] as $name) {
 App\Models\User::factory()->create(['name'=>'Synthetic'.$name,'email'=>strtolower($name).'@example.test','password'=>Illuminate\Support\Facades\Hash::make('Synthetic-only-Password123!'),'email_verified_at'=>$name==='Unverified'?null:now()]);
}
$c=App\Models\Category::create(['name'=>'synthetic','title'=>'Synthetic category','description'=>'Test only','url'=>'/trophy.svg']);
$p=App\Models\Platform::create(['name'=>'synthetic','title'=>'Synthetic platform','description'=>'Test only']);
$r=new App\Models\Speedrun(['url'=>'https://youtu.be/TEST0000001','time'=>'12.3456']);
$r->user_id=1;$r->save();$r->categories()->attach($c->id);$r->platforms()->attach($p->id);
echo "Synthetic fixture ready; observed-live auth overlay enabled.\n";
