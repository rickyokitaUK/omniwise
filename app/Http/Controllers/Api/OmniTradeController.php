<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class OmniTradeController extends Controller
{
    /**
     * 1) Create order (buy/sell)
     */
    public function create(Request $req, BinanceController $binance)
    {
        $data = $req->validate([
            'symbol'   => ['required','string'],
            'side'     => ['required', Rule::in(['BUY','SELL'])],
            'type'     => ['required', Rule::in(['MARKET','LIMIT'])],
            'quantity' => ['required','numeric','gt:0'],
            'price'    => ['nullable','numeric','gt:0'],
            'testnet'  => ['nullable','boolean'],
        ]);

        // Generate a unique clientOrderId for tracking
        $clientOrderId = 'ow_' . Str::uuid()->toString();
        $req->merge(['clientOrderId' => $clientOrderId]);

        /**
         * Delegate directly to your existing BinanceController::trade()
         * 
         * That controller already:
         * - Loads UserApiKey from DB for the authenticated user
         * - Decrypts secret key
         * - Calls Binance testnet or live
         * - Logs to DB if necessary
         * - Returns real JSON
         */
        $response = $binance->trade($req);

        if (is_array($response)) {
            return response()->json([
                'clientOrderId' => $clientOrderId,
                'binance'       => $response,
            ]);
        }

        // Add clientOrderId so 3rd-party can track it
        return response()->json([
            'clientOrderId' => $clientOrderId,
            'binance'       => $response->getData(true),
        ]);
    }

    /**
     * 2) Show trades (history)
     */
    public function trades(Request $req, BinanceController $binance)
    {
        // Simply reuse your existing BinanceController::trades()
        $response = $binance->trades($req);

        return response()->json($response);
    }

    /**
     * 3) Get Balance
     */
    public function balance(Request $req, BinanceController $binance)
    {
        // Reuse your existing BinanceController::balance()
        $response = $binance->balance($req);

        return response()->json($response);
    }

    /**
     * (Optional) show single order by clientOrderId
     * — if needed you can map clientOrderId to Binance orderId
     */
    public function show(Request $req, string $clientOrderId)
    {
        return response()->json([
            'clientOrderId' => $clientOrderId,
            'message' => 'Lookup by clientOrderId is not implemented yet.'
        ], 501);
    }

    /**
     * (Optional) cancel by clientOrderId
     */
    public function cancel(Request $req, string $clientOrderId)
    {
        return response()->json([
            'clientOrderId' => $clientOrderId,
            'message' => 'Cancel by clientOrderId is not implemented yet.'
        ], 501);
    }
}
