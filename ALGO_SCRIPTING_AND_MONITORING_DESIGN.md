# Algo Scripting & Heartbeat Monitoring Design

## 1. Algo Scripting Interface

**Objective**: Allow backend developers to script trading logic in a separate file/interface without modifying the core PHP code every time strategies change.

**Solution**: File-Based Policy Loader with Hot-Reloading.

### Workflow
1.  **Script File**: The developer writes the strategy in a text file (DSL format).
    *   Location: `storage/app/algo/strategy.txt`
    *   Format:
        ```text
        IF MA4 > MA9 THEN BUY
        IF MA4 < MA9 THEN SELL
        ```
2.  **Execution Engine**:
    *   `TickBufferService` (or a dedicated `TradeExecutorService`) reads this file before every decision tick.
    *   To prevent disk I/O overhead on every tick (millisec), cache the file content and check `filemtime` (last modified time). If changed, reload.

### Implementation Details

#### Step 1: Create the Strategy File
Create directory `storage/app/algo` and file `strategy.txt`.

#### Step 2: Policy Loader Helper (`app/Services/PolicyLoader.php`)
```php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class PolicyLoader
{
    protected string $path = 'algo/strategy.txt';

    public function getPolicy(): string
    {
        // Simple file get, or use Cache::remember with filemtime check
        if (!Storage::exists($this->path)) {
            return ''; // Default or empty
        }
        return Storage::get($this->path);
    }
}
```

#### Step 3: Inject into TickBufferService
Modify `TickBufferService` to use `PolicyLoader` instead of hardcoded strings.
```php
// In TickBufferService::flushMinute
$policy = $this->policyLoader->getPolicy();
$decision = $this->algoPolicy->decide($indicators, $policy);
```

### Alternative: Web Interface
For a UI, create a simple Controller/View:
*   `GET /admin/algo` -> Show textarea with current content.
*   `POST /admin/algo` -> Save content to `storage/app/algo/strategy.txt`.

---

## 2. Heartbeat & Signal Monitor

**Objective**: Monitor a "URL signal" from another system and verify if Open/Close position commands match (Reconciliation).

**Interpretation**:
*   **External System** (System A) sends a "Signal" (e.g., "I am Long") to simple endpoint.
*   **Omniwise** (System B) receives it.
*   **Heartbeat Logic**: Compare "External Signal" vs "Internal Position Status". Alert if mismatch.

### Workflow
1.  **Signal Endpoint**: Create an API endpoint for the external system to push its state.
    *   `POST /api/v1/external-signal`
    *   Payload: `{ "signal": "BUY", "timestamp": 12345678, "source": "TradingView" }`
2.  **Storage**: Save this last signal in Cache or DB (`external_signals` table).
3.  **Verification (Heartbeat)**:
    *   Scheduled Task (e.g., every 1 min) or Real-time check.
    *   Logic:
        *   Get `Latest_External_Signal` (e.g., BUY).
        *   Get `Current_Binance_Position` (from `BinanceService::getAccountInfo` or local DB).
        *   If `External == BUY` AND `Binance == NO_POSITION` -> **MISMATCH ALERT**.
        *   If `External == SELL` AND `Binance == LONG` -> **MISMATCH ALERT**.

### Implementation Details

#### Step 1: Create Controller for Signal Receiver
`app/Http/Controllers/Api/SignalController.php`
```php
public function receive(Request $req) {
    // Save signal to Cache for fast access
    Cache::put('latest_external_signal', $req->input('signal'), 600); // 10 min TTL
    // Log for audit
    Log::info("External Signal Received: " . $req->input('signal'));
}
```

#### Step 2: Verification Command
`app/Console/Commands/VerifyHeartbeat.php`
```php
public function handle(BinanceService $binance) {
    $signal = Cache::get('latest_external_signal');
    if (!$signal) return;

    $account = $binance->getAccountInfo();
    // Logic to parse account info and find if we have position
    $hasLong = ...; 

    if ($signal === 'BUY' && !$hasLong) {
        Log::emergency("HEARTBEAT FAILURE: Signal is BUY but no position found!");
        // Optional: Trigger emergency buy or slack alert
    }
}
```

## Summary of Files to Create/Amend
1.  `app/Services/PolicyLoader.php` (New)
2.  `storage/app/algo/strategy.txt` (New)
3.  `app/Services/TickBufferService.php` (Amend to use Loader)
4.  `routes/api.php` (Add Signal Endpoint logic)
5.  `app/Http/Controllers/Api/SignalController.php` (New)
6.  `app/Console/Commands/VerifyHeartbeat.php` (New)
