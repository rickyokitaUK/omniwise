<?php

use App\Services\TickBufferService;
use App\Services\AlgoPolicyService;
use App\Services\BinanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting TickBufferService Test...\n";

// 1. Mock AlgoPolicyService
$mockAlgo = Mockery::mock(AlgoPolicyService::class);
$mockAlgo->shouldReceive('decide')
    ->andReturn('BUY'); // Force a BUY decision for testing

echo "Mocked AlgoPolicyService: Configured to return 'BUY'.\n";

// 2. Mock BinanceService
$mockBinance = Mockery::mock(BinanceService::class);
$mockBinance->shouldReceive('placeMarketOrder')
    ->withArgs(function ($symbol, $side, $quantity) {
        echo ">>> BinanceService::placeMarketOrder called!\n";
        echo "    Symbol: $symbol\n";
        echo "    Side:   $side\n";
        echo "    Qty:    $quantity\n";
        return $symbol === 'SOLUSDT' && $side === 'BUY';
    })
    ->once()
    ->andReturn(['orderId' => 12345]);

echo "Mocked BinanceService: Expecting placeMarketOrder('SOLUSDT', 'BUY').\n";

// 3. Clean DB for test
DB::table('solana_tick_batches')->truncate();
echo "Database truncated (solana_tick_batches).\n";

// 4. Instantiate Service
$service = new TickBufferService($mockAlgo, $mockBinance);

// 5. Simulate 10 minutes of data to fill MA9
// We need enough batches so MA9 is not null.
// TickBufferService calculates MA on "flush", so we need to validly trigger N flushes.
// But the code checks current DB state.
// Let's seed the DB with 9 previous batches first to simplify.

$now = time();
for ($i = 10; $i > 1; $i--) {
    DB::table('solana_tick_batches')->insert([
        'minute' => date('Y-m-d H:i:00', $now - ($i * 60)),
        'ticks' => '[]',
        'tick_count' => 10,
        'close' => 100 + $i, // Price increasing
        'ma4' => 100 + $i,
        'ma9' => 100 + $i,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
echo "Seeded 9 historical batches.\n";

// 6. Add ticks to trigger a flush of the "current" minute
// We add a tick for Minute X, then a tick for Minute X+1.
// The transition triggers flush of Minute X.

$minuteX = ($now - 60) * 1000;      // 1 minute ago
$minuteNext = $now * 1000;          // Now

// Add ticks for Minute X
// Note: In the actual code, tick uses 'price'/'qty' keys after parsing from websocket, 
// OR raw from websocket might be different. 
// Let's check StreamSolanaFromBinance again.
// It passes ['price'=>..., 'qty'=>..., 'event_time'=>...] to addTick.

$service->addTick(['price' => 150.0, 'qty' => 1.0, 'event_time' => $minuteX]);
$service->addTick(['price' => 152.0, 'qty' => 1.0, 'event_time' => $minuteX + 1000]);

// Add tick for Minute Next -> This triggers flush of Minute X
echo "Triggering flush...\n";
$service->addTick(['price' => 155.0, 'qty' => 1.0, 'event_time' => $minuteNext]);

echo "Test Finished.\n";
