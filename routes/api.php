<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\BinanceController;

// === Third-party endpoints ===
use App\Http\Controllers\Api\OmniTradeController;
use App\Http\Controllers\Api\IndicatorController;
use App\Http\Controllers\Api\AlgoController;
use App\Http\Controllers\Api\SolanaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| 這裡定義 API 路由。Laravel 會自動將它們掛載在 /api 前綴下。
| Public 路由不需要登入，Protected 路由需經過 auth:sanctum 驗證。
|
*/

// ---------- Public API endpoints ----------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ---------- Protected API endpoints (需要登入 / Token 驗證) ----------
Route::middleware('auth:sanctum')->group(function () {
    // ✅ 登出：需要使用者登入才能執行
    Route::post('/logout', [AuthController::class, 'logout']);

    // ✅ 使用者資料：登入後取得 user id, username, email
    // 原本在群組外會導致 $request->user() == null
    Route::get('/me', [AuthController::class, 'me']);

    // ✅ Binance 相關 API：全數搬入受保護群組內，確保能正確讀取 $request->user()
    Route::prefix('binance')->group(function () {
        // 取得即時價格 (可視需求改為公開)
        Route::get('price/{symbol}',          [BinanceController::class, 'price']);

        // 取得帳號資訊 (需登入)
        Route::get('account/{username}',      [BinanceController::class, 'accountInfo']);

        // 取得當前開啟訂單 (需登入)
        Route::get('orders/{username}',       [BinanceController::class, 'openOrders']);

        // 下市價單 (需登入)
        Route::post('trade/{username}',       [BinanceController::class, 'placeMarketOrder']);

        // ✅ 新增：需要登入才能取得金鑰設定
        Route::get('keys',                    [BinanceController::class, 'keys']);

        // ✅ 新增：需要登入才能查看餘額
        Route::get('balance',                 [BinanceController::class, 'balance']);

        // ✅ 新增：需要登入才能送出下單請求
        Route::post('trade',                  [BinanceController::class, 'trade']);

        Route::get('trades', [BinanceController::class, 'trades']);

        // ✅ 新增：需要登入才能查看 funding 帳戶餘額
        Route::get('funding-balance',         [BinanceController::class, 'fundingBalance']);
        });

       


        // Technical indicators
    Route::get('/v1/indicators/{symbol}', [IndicatorController::class, 'show']); // ?interval=1m&limit=50

    // LLM 決策（1分鐘決策原型）
    Route::post('/v1/algo/decision', [AlgoController::class, 'decide']); // body: {symbol, interval, policy}
    
    
    Route::prefix('omnitrade')->group(function () {
        Route::get('balance',                 [OmniTradeController::class, 'balance']);
        Route::post('orders',                 [OmniTradeController::class, 'create']);
		Route::get('trades', [OmniTradeController::class, 'trades']);

        Route::get('orders/{clientOrderId}',  [OmniTradeController::class, 'show']); 
        Route::post('orders/{clientOrderId}/cancel', [OmniTradeController::class, 'cancel']);
        });
});


Route::get('/solana/latest', [SolanaController::class, 'latest']);
Route::get('/solana/history', [SolanaController::class, 'history']);

 // OmniTrade related endpoints
 

 Route::get('/test', function () {
    return 'API WORKING';
});

