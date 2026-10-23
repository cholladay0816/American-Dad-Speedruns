<?php
if (getenv('APP_ENV') !== 'testing' || getenv('DB_DATABASE') !== ':memory:') {
    fwrite(STDERR, "Refusing non-test database.\n"); exit(2);
}
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Mail::fake();
Illuminate\Support\Facades\Http::fake();
Illuminate\Support\Facades\Storage::fake('public');
$checks = [];
$record = function($name, $ok) use (&$checks) { $checks[]=['check'=>$name,'passed'=>(bool)$ok]; };
try {
    Illuminate\Support\Facades\Artisan::call('migrate', ['--force'=>true]);
    $record('fresh synthetic SQLite migrations', true);
    $u = App\Models\User::factory()->create(['name'=>'SyntheticRunner','email'=>'runner@example.test']);
    $category = App\Models\Category::create(['name'=>'synthetic','title'=>'Synthetic category','description'=>'Test only','url'=>'http://127.0.0.1/test.png']);
    $platform = App\Models\Platform::create(['name'=>'synthetic','title'=>'Synthetic platform','description'=>'Test only']);
    $run = new App\Models\Speedrun(['url'=>'https://youtu.be/TEST0000001','time'=>'12.3456']);
    $run->user_id=$u->id; $run->save();
    $run->categories()->attach($category->id);
    $run->platforms()->attach($platform->id);
    $record('synthetic run owner preserved', (string)$run->fresh()->user_id === (string)$u->id && $run->fresh()->user->is($u));
    $record('synthetic decimal round trip', abs((float)$run->fresh()->time-12.3456)<0.0000001);
    $record('SQLite foreign keys enabled', (int)Illuminate\Support\Facades\DB::select('PRAGMA foreign_keys')[0]->foreign_keys === 1);
    $record('SQLite integrity', Illuminate\Support\Facades\DB::select('PRAGMA integrity_check')[0]->integrity_check === 'ok');
    $record('SQLite foreign key integrity', count(Illuminate\Support\Facades\DB::select('PRAGMA foreign_key_check')) === 0);
    Illuminate\Support\Facades\Mail::assertNothingSent();
    Illuminate\Support\Facades\Mail::assertNothingQueued();
    $record('no mail side effects', true);
} catch (Throwable $e) {
    $checks[]=['check'=>'synthetic baseline execution','passed'=>false,'error'=>get_class($e),'message'=>substr($e->getMessage(),0,500)];
}
echo json_encode(['scope'=>'initial smoke only, not full regression coverage','checks'=>$checks], JSON_PRETTY_PRINT),"\n";
exit(count(array_filter($checks,fn($c)=>!$c['passed'])) ? 1 : 0);
