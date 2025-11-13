<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use App\Models\UserApiKey;
use Exception;

class BinanceController extends Controller
{
    /**
     * ✅ 從資料庫取出使用者的 API Key/Secret（支援 testnet）
     */
protected function credentials($userId, $useTestnet = false)
{
    $rec = \App\Models\UserApiKey::where('user_id', $userId)
        ->where('exchange', 'binance')
        ->where('use_testnet', $useTestnet)
        ->firstOrFail();

        if (!$rec) {
            return null; // <-- SAFE: return null so controller returns 404
        }


    // ✅ 修正：如果欄位是 'secret'，改這行
    $secretValue = $rec->secret_key ?? $rec->secret ?? null;

    if (!$secretValue) {
        throw new \Exception("Missing secret_key for user {$userId}");
    }

    $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($secretValue);

    \Log::info('🔐 Binance Key Debug', [
        'user_id' => $userId,
        'use_testnet' => $useTestnet,
        'api_key' => $rec->api_key,
        'secret_preview' => substr($decrypted, 0, 4) . '...' . substr($decrypted, -4),
        'exchange' => $rec->exchange
    ]);

    return [
        'key'    => $rec->api_key,
        'secret' => $decrypted,
        'testnet'=> (bool)$rec->use_testnet
    ];
}


    /**
     * ✅ 回傳 API Key 狀態（含 secret key）
     */
    public function keys(Request $request)
    {
        $user = $request->user();
        $record = UserApiKey::where('user_id', $user->userid)
            ->where('exchange', 'binance')
            ->select('api_key', 'secret_key', 'use_testnet', 'ip_address')
            ->first();

        if (!$record) {
            return response()->json(['error' => 'No Binance key found'], 404);
        }

        return response()->json([
            'api_key' => $record->api_key,
            'secret_key' => Crypt::decryptString($record->secret_key),
            'use_testnet' => $record->use_testnet,
            'ip_address' => $record->ip_address,
        ]);
    }

    /**
     * ✅ 取得最新報價
     */
    public function price(Request $request)
    {
        $symbol = $request->query('symbol', 'BTCUSDT');
        $url = "https://api.binance.com/api/v3/ticker/price?symbol={$symbol}";

        $res = Http::get($url);
        return $res->json();
    }

    /**
     * ✅ 查詢現有掛單（pending orders）
     */
    public function openOrders(Request $request)
    {
        try {
            $user = $request->user();
            $useTestnet = $request->boolean('testnet', false);
            $c = $this->credentials($user->userid, $useTestnet);

            $base = $c['testnet']
                ? 'https://testnet.binance.vision/api'
                : 'https://api.binance.com/api';

            $timestamp = round(microtime(true) * 1000);
            $query = "timestamp={$timestamp}";
            $signature = hash_hmac('sha256', $query, $c['secret_key']);

            $url = "{$base}/v3/openOrders?{$query}&signature={$signature}";
            $res = Http::withHeaders(['X-MBX-APIKEY' => $c['api_key']])->get($url);

            return $res->json();
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ 下單 (支援 MARKET / LIMIT)
     */
public function trade(Request $request)
{
    try {
        $data = $request->validate([
            'symbol' => 'required|string',
            'side' => 'required|in:BUY,SELL',
            'quantity' => 'required|numeric',
            'type' => 'nullable|string',
            'price' => 'nullable|numeric',
        ]);

        $user = $request->user();
        $useTestnet = $request->boolean('testnet', false);
        $c = $this->credentials($user->userid, $useTestnet);

        // ✅ 正確設定 base URL
        $base = $c['testnet']
            ? 'https://testnet.binance.vision/api'
            : 'https://api.binance.com/api';

        // ✅ 準備訂單參數
        $type = strtoupper($data['type'] ?? 'MARKET');
        $timestamp = round(microtime(true) * 1000);

        $params = [
            'symbol' => strtoupper($data['symbol']),
            'side' => strtoupper($data['side']),
            'type' => $type,
            'quantity' => $data['quantity'],
            'timestamp' => $timestamp,
        ];

        if ($type === 'LIMIT') {
            $params['timeInForce'] = 'GTC'; // Good Til Cancelled
            $params['price'] = $data['price'];
        }

        // ✅ 簽名與 URL
        $query = http_build_query($params);
        $signature = hash_hmac('sha256', $query, $c['secret']);
        $params['signature'] = $signature;

        $url = "{$base}/v3/order";

        \Log::info('🚀 Binance Trade Request', [
            'user_id' => $user->userid ?? null,
            'url' => $url,
            'params' => $params,
            'base' => $base,
            'testnet' => $c['testnet'],
            'api_key_preview' => substr($c['key'], 0, 6) . '...' . substr($c['key'], -6),
        ]);

        // ✅ 發送請求
        $res = Http::withHeaders(['X-MBX-APIKEY' => $c['key']])
            ->asForm()
            ->post($url, $params);

        \Log::info('🧾 Binance Trade Response', [
            'status' => $res->status(),
            'body' => $res->body(),
        ]);

        // ✅ 處理 Binance 錯誤代碼
        if ($res->failed()) {
            return response()->json([
                'error' => 'Binance Error',
                'status' => $res->status(),
                'response' => $res->json(),
            ], $res->status());
        }

        return $res->json();

    } catch (\Throwable $e) {
        \Log::error('❌ Binance Trade Exception', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
}


    /**
     * ✅ 取得帳戶餘額
     */
public function balance(Request $request)
{
    try {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $useTestnet = $request->boolean('testnet', false);
        $c = $this->credentials($user->userid, $useTestnet);

        if (!$c) {
            return response()->json([
                "error" => "No Binance API key found for this user."
            ], 404);
        }

        \Log::info('🔑 Using Binance Credentials', [
    'user_id' => $user->userid ?? null,
    'from_database' => $c,
]);

        // ✅ 正確的 API Host
        $base = $c['testnet']
            ? 'https://testnet.binance.vision/api'
            : 'https://api.binance.com/api';

        $timestamp = round(microtime(true) * 1000);
        $query = "timestamp={$timestamp}";

        // ✅ 確保正確使用 secret
        $signature = hash_hmac('sha256', $query, $c['secret']);

        $url = "{$base}/v3/account?{$query}&signature={$signature}";

        // ✅ 用正確的 key
        $res = Http::withHeaders(['X-MBX-APIKEY' => $c['key']])->get($url);

        \Log::info('🧾 Binance Debug', [
            'user_id' => $user->userid,
            'use_testnet' => $useTestnet,
            'base' => $base,
            'url' => $url,
            'status' => $res->status(),
            'body' => substr($res->body(), 0, 300),
            'api_key_preview' => substr($c['key'], 0, 6) . '...' . substr($c['key'], -4),
        ]);

        return $res->json();
    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
}

public function trades(Request $request)
{
    try {
        $user = $request->user();
        $useTestnet = $request->boolean('testnet', false);
        $symbol = strtoupper($request->query('symbol', 'BTCUSDT')); // optional filter

        $c = $this->credentials($user->userid, $useTestnet);

        $base = $c['testnet']
            ? 'https://testnet.binance.vision/api'
            : 'https://api.binance.com/api';

        $timestamp = round(microtime(true) * 1000);
        $query = http_build_query([
            'symbol' => $symbol,
            'timestamp' => $timestamp,
        ]);

        $signature = hash_hmac('sha256', $query, $c['secret']);
        $url = "{$base}/v3/myTrades?{$query}&signature={$signature}";

        \Log::info('📜 Fetching Binance Trades', [
            'user_id' => $user->userid ?? null,
            'symbol' => $symbol,
            'url' => $url,
        ]);

        $res = Http::withHeaders(['X-MBX-APIKEY' => $c['key']])->get($url);

        if ($res->failed()) {
            \Log::error('❌ Binance Trade History Error', [
                'status' => $res->status(),
                'body' => $res->body(),
            ]);
            return response()->json(['error' => $res->json()], $res->status());
        }

        return $res->json();
    } catch (\Throwable $e) {
        \Log::error('❌ Exception in trades()', ['message' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


}
