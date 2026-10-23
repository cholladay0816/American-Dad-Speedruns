<?php
// Run only inside the disposable baseline container, with --network none.
if (getenv('APP_ENV') !== 'testing' || getenv('DB_DATABASE') !== ':memory:') {
    fwrite(STDERR, "Refusing non-test environment.\n"); exit(2);
}
require '/app/vendor/autoload.php';
$app = require '/app/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\Mail::fake();
Illuminate\Support\Facades\Http::fake();
$rows = [];
foreach ($app['router']->getRoutes() as $route) {
    $uri = $route->uri();
    $retired = preg_match('~^(billing-portal|council|elections|stripe)(/|$)~', $uri) === 1;
    $rows[] = ['methods'=>$route->methods(), 'uri'=>$uri, 'name'=>$route->getName(),
        'middleware'=>$route->gatherMiddleware(), 'action'=>$route->getActionName(),
        'scope'=>$retired ? 'retired-discrepancy' : 'inventory-review-required'];
}
usort($rows, fn($a,$b)=>strcmp($a['uri'],$b['uri']));
echo json_encode(['baseline_commit'=>'44917962a8f644a1e05a20e66fb19db7bf9851b1',
    'production_behavior_verified'=>false, 'routes'=>$rows], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES), "\n";
