<?php
if(getenv('APP_ENV')!=='testing'||getenv('DB_DATABASE')!=='/fixture/restored-legacy-copy.sqlite'){exit(2);}
foreach(['storage/framework/cache/data','storage/framework/sessions','storage/framework/views','storage/logs','bootstrap/cache'] as $dir){if(!is_dir('/app/'.$dir))mkdir('/app/'.$dir,0777,true);}
putenv('APP_KEY=base64:'.base64_encode(random_bytes(32)));
require '/app/vendor/autoload.php';$app=require '/app/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.connections.sqlite.options'=>[PDO::SQLITE_ATTR_OPEN_FLAGS=>PDO::SQLITE_OPEN_READONLY]]);
Illuminate\Support\Facades\Mail::fake();Illuminate\Support\Facades\Notification::fake();Illuminate\Support\Facades\Http::fake();
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);$routes=[];
foreach(['/login','/speedruns','/categories/synthetic','/watch/1'] as $path){$request=Illuminate\Http\Request::create($path,'GET');$response=$kernel->handle($request);$routes[$path]=$response->getStatusCode();$kernel->terminate($request,$response);}
$db=Illuminate\Support\Facades\DB::connection();$result=['php'=>PHP_VERSION,'laravel'=>$app->version(),'sourceBase'=>'44917962a8f644a1e05a20e66fb19db7bf9851b1','databaseReadOnly'=>true,'routes'=>$routes,'integrity'=>$db->select('PRAGMA integrity_check')[0]->integrity_check,'foreignKeyViolations'=>count($db->select('PRAGMA foreign_key_check')),'tables'=>count($db->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")),'mailQueued'=>false,'productionTouched'=>false];
Illuminate\Support\Facades\Mail::assertNothingQueued();Illuminate\Support\Facades\Notification::assertNothingSent();$result['passed']=count(array_filter($routes,function($status){return $status!==200;}))===0&&$result['integrity']==='ok'&&$result['foreignKeyViolations']===0;file_put_contents('/evidence/legacy-boot.json',json_encode($result,JSON_PRETTY_PRINT));echo json_encode($result),PHP_EOL;exit($result['passed']?0:1);
