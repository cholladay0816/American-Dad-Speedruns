<?php
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$file=realpath('/app/public'.$path);
if ($path!=='/' && $file && str_starts_with($file,'/app/public/') && is_file($file)) { return false; }
$app=require __DIR__.'/bootstrap-browser.php';
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
Illuminate\Support\Facades\Mail::fake();
Illuminate\Support\Facades\Notification::fake();
Illuminate\Support\Facades\Http::fake();
$response=$kernel->handle($request=Illuminate\Http\Request::capture());
$response->send();$kernel->terminate($request,$response);
