<?php
$app=require __DIR__.'/bootstrap-browser.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$tokens=[];
foreach(['chromium','firefox','webkit','mobile-chrome','mobile-safari'] as $project){
 foreach(['reset','delete','sessions'] as $kind){
  $u=App\Models\User::factory()->create(['name'=>'Synthetic'.$kind.str_replace('-','',$project),'email'=>$kind.'-'.$project.'@example.test','password'=>Illuminate\Support\Facades\Hash::make('Synthetic-only-Password123!'),'email_verified_at'=>null]);
  if($kind==='reset')$tokens[$project]=Illuminate\Support\Facades\Password::broker()->createToken($u);
 }
}
file_put_contents('/tmp/adsr-reset-fixtures.json',json_encode($tokens));
