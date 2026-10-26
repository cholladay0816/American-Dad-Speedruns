<?php
// Root-owned installed helper. Output is deliberately limited to nonsecret configuration.
try {
    require getcwd().'/vendor/autoload.php';
    $app = require getcwd().'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    if (PHP_VERSION !== '8.5.12') {
        throw new RuntimeException('Unreviewed PHP runtime');
    }
    echo json_encode([
        'key_present' => is_string(config('app.key')) && strlen(config('app.key')) > 0,
        'debug' => config('app.debug'),
        'driver' => config('database.default'),
        'database' => config('database.connections.sqlite.database'),
        'mailer' => config('mail.default'),
        'session' => config('session.driver'),
        'cookie' => config('session.cookie'),
        'queue' => config('queue.default'),
        'failed_driver' => config('queue.failed.driver'),
        'retry_after' => config('queue.connections.database.retry_after'),
        'timezone' => config('app.timezone'),
        'pcntl' => extension_loaded('pcntl'),
    ], JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    fwrite(STDERR, "Application preflight failed; no configuration values disclosed.\n");
    exit(1);
}
