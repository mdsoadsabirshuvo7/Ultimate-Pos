<?php

$output = shell_exec('cd .. && git pull origin main 2>&1');
echo "<pre><h3>Git Pull Output:</h3>" . htmlspecialchars($output) . "</pre>";

try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    \Artisan::call('optimize:clear');
    \Artisan::call('view:clear');
    \Artisan::call('route:clear');
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');

    echo "Cache cleared successfully. Artisan output: <br>\n";
    echo nl2br(\Artisan::output());

    // Fallback manual delete
    foreach (glob(__DIR__.'/../bootstrap/cache/*.php') as $file) {
        if (basename($file) !== '.gitignore') {
            @unlink($file);
        }
    }
    echo "<br>Bootstrap cache files cleared.";
} catch (\Exception $e) {
    echo "<h3>Error clearing cache:</h3>" . $e->getMessage();
}
