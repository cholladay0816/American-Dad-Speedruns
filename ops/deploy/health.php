<?php
// Installed as public/_adsr-health.php. Apache must also restrict this URI to local clients.
ini_set('display_errors', '0');
header('Content-Type: application/json');
header('Cache-Control: no-store');
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit;
}
try {
    $release = realpath(__DIR__.'/..');
    require $release.'/vendor/autoload.php';
    $app = require $release.'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    if (!is_file(config('database.connections.sqlite.database'))) {
        throw new RuntimeException('Missing persistent DB');
    }
    // No HTTP session middleware, writes, mail, jobs, external calls or migrations.
    $app->make('db')->connection()->select('SELECT 1');
    $identity = json_decode(file_get_contents($release.'/.adsr-release.json'), true, 8, JSON_THROW_ON_ERROR);
    echo json_encode(['ok' => true, 'commit' => $identity['commit'], 'release' => $release], JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    http_response_code(503);
    echo '{"ok":false}';
}
