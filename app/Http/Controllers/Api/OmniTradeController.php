<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class OmniTradeController extends Controller
{
    public function create(Request $req)
    {
        $data = $req->validate([
            'symbol'   => ['required','string'],
            'side'     => ['required', Rule::in(['BUY','SELL'])],
            'type'     => ['required', Rule::in(['MARKET','LIMIT'])],
            'quantity' => ['required','numeric','gt:0'],
            'price'    => ['nullable','numeric','gt:0'],
            'testnet'  => ['nullable','boolean'],
        ]);

        // 產 clientOrderId 讓三方追蹤
        $clientOrderId = 'ow_' . Str::uuid()->toString();

        // TODO: 把實際下單交給你現成的 BinanceController 或 service。
        // 這裡給出最小可運作的範例（依你專案既有取 key 方式）：
        // 假設你已有方法 getUserBinanceCred($user, $useTestnet) 取 API Key/Secret
        // 並有一個 TradeService->placeOrder(...) 你可以自行接回去。

        return response()->json([
            'clientOrderId' => $clientOrderId,
            'status' => 'ACCEPTED', // 原型：接受請求；真實下單結果可在此回傳
        ], 202);
    }

    public function show(Request $req, string $clientOrderId)
    {
        // 原型：回傳假資料；接回你現有的 trades 查詢即可
        return response()->json([
            'clientOrderId' => $clientOrderId,
            'exchangeOrderId' => null,
            'status' => 'NEW', // NEW | FILLED | PARTIALLY_FILLED | CANCELED | REJECTED
            'filledQty' => 0,
        ]);
    }

    public function cancel(Request $req, string $clientOrderId)
    {
        // 原型：示意撤單成功（接回 Binance 真實 cancel 即可）
        return response()->json([
            'clientOrderId' => $clientOrderId,
            'status' => 'CANCELED',
        ]);
    }
}
