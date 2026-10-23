<?php
$app=require __DIR__.'/bootstrap-browser.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Mail::fake();Illuminate\Support\Facades\Http::fake();
$c=App\Models\Category::create(['name'=>'ranking-fixture','title'=>'Synthetic rankings','description'=>'Synthetic populated checks','url'=>'/trophy.svg']);$p=App\Models\Platform::first();$ids=[];
for($i=0;$i<25;$i++){$u=App\Models\User::factory()->create(['name'=>'SyntheticRank'.str_pad($i,2,'0',STR_PAD_LEFT),'email'=>'rank'.$i.'@example.test']);$r=App\Models\Speedrun::create(['user_id'=>$u->id,'url'=>'https://youtu.be/RANK'.str_pad($i,7,'0',STR_PAD_LEFT),'time'=>$i<2?'1.23456':(string)($i+1),'verified'=>1]);$r->categories()->attach($c);$r->platforms()->attach($p);$ids[]=$r->id;if($i===24)$r->disqualify('Synthetic ordering fixture');}
file_put_contents('/tmp/adsr-ranking-fixture.json',json_encode(['ids'=>$ids]));
