# Instructions for Backend Developer

## Objective
Implement automated trading logic (Open/Settle Position) based on MA4 and MA9 indicators.

## Current System Analysis
1.  **Data Streaming**: `StreamSolanaFromBinance` command streams trades via WebSocket.
2.  **Data Aggregation**: `TickBufferService::addTick` aggregates ticks into 1-minute batches.
3.  **Indicator Calculation**: `TickBufferService::flushMinute` calculates **MA4** and **MA9** at the end of each minute and stores them in the `solana_tick_batches` table.
4.  **Missing Component**: Currently, the system calculates indicators but **does not act on them**. There is no trigger to execute a trade based on the calculated Moving Averages.

## Implementation Plan

You need to amend **`app/Services/TickBufferService.php`** to trigger the trading logic immediately after the MA calculation.

### File to Amend
`app/Services/TickBufferService.php`

### Required Changes

1.  **Inject Dependencies**:
    Inject `AlgoPolicyService` (for decision making) and `BinanceService` (for trade execution) into `TickBufferService`.

2.  **Implement Trading Logic in `flushMinute`**:
    At the end of the `flushMinute` method (after MA4/MA9 are calculated/stored), add the logic to:
    *   Construct the `indicators` array.
    *   Retrieve the trading policy (e.g., "IF MA4 > MA9 THEN BUY").
    *   Get a decision from `AlgoPolicyService`.
    *   Execute the trade using `BinanceService`.

### Code Snippet Reference

Here is how you should modify `app/Services/TickBufferService.php`:

```php
// ... imports
use App\Services\AlgoPolicyService;
use App\Services\BinanceService;

class TickBufferService
{
    // ... existing properties

    protected AlgoPolicyService $algoPolicy;
    protected BinanceService $binanceService;

    // Inject services via Constructor (or use app() helper if strictly service-based without DI container resolution in legacy command usage)
    public function __construct(AlgoPolicyService $algoPolicy, BinanceService $binanceService)
    {
        $this->algoPolicy = $algoPolicy;
        $this->binanceService = $binanceService;
    }

    // ... existing addTick method

    protected function flushMinute(int $minute)
    {
        // ... existing logic to save batch ...
        
        // ... existing logic to calculate MA4 / MA9 ...
        // $ma4 = ...
        // $ma9 = ...
        
        // ... existing DB update ...

        // === ADD THIS TRADING LOGIC ===
        
        if ($ma4 !== null && $ma9 !== null) {
            $indicators = [
                'MA4'     => $ma4,
                'MA9'     => $ma9,
                'Nominal' => $close,
            ];

            // TODO: Retrieve policy from DB or Config. Example:
            // $policy = "IF MA4 > MA9 THEN BUY\nIF MA4 < MA9 THEN SELL"; 
            // For now, you might fetch it from a User's setting or a global config.
            $policy = "IF MA4 > MA9 THEN BUY\nIF MA4 < MA9 THEN SELL"; 

            try {
                $decision = $this->algoPolicy->decide($indicators, $policy);

                if ($decision === 'BUY' || $decision === 'SELL') {
                    // Define quantity to trade (e.g., fixed amount or percentage of balance)
                    $quantity = '1.0'; // Example: 1 SOL
                    
                    // Execute Trade
                    $this->binanceService->placeMarketOrder('SOLUSDT', $decision, $quantity);
                    
                    // Log the trade
                    \Illuminate\Support\Facades\Log::info("Auto-Trade Executed: $decision at $close (MA4: $ma4, MA9: $ma9)");
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Trade execution failed: " . $e->getMessage());
            }
        }
    }
}
```

## Summary of Logic Flow
1.  **Get Data**: `StreamSolanaFromBinance` receives tick.
2.  **Process**: `TickBufferService` calculates MA4/MA9.
3.  **Decide**: `AlgoPolicyService` compares MA4 and MA9.
4.  **Act**: `BinanceService` sends HTTP Request to Binance to Open/Settle position (Buy/Sell).
