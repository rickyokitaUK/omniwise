<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "DB Host: " . config('database.connections.mysql.host') . "\n";
echo "DB Port: " . config('database.connections.mysql.port') . "\n";
echo "DB Database: " . config('database.connections.mysql.database') . "\n";
echo "DB Username: " . config('database.connections.mysql.username') . "\n";

echo "Testing override host to ::1\n";
config(['database.connections.mysql.host' => '::1']);
try {
    \Illuminate\Support\Facades\DB::reconnect();
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "Connection successful with ::1!\n";
} catch (\Exception $e) {
    echo "Connection failed with ::1: " . $e->getMessage() . "\n";
}
