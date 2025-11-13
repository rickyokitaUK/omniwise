<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BinanceService
{
    protected string $apiKey;
    protected string $secretKey;
    protected string $baseUrl;
    protected bool $useTestnet = true;

    /**
     * @param  string|null  $apiKey     – user’s API key (load from DB)
     * @param  string|null  $secretKey  – user’s Secret key (decrypt from DB)
     * @param  bool         $useTestnet – true for testnet, false for live
     */
    public function __construct(?string $apiKey = null, ?string $secretKey = null)
    {
        $this->apiKey    = $apiKey    ?? config('services.binance.key');
        $this->secretKey = $secretKey ?? config('services.binance.secret');
        $this->baseUrl   = $this->useTestnet
            ? 'https://testnet.binance.vision/api'
            : 'https://api.binance.com/api';
    }

    /**
     * Public endpoint – get latest ticker price
     */
    public function getTickerPrice(string $symbol): array
    {
        return Http::get("{$this->baseUrl}/v3/ticker/price", [
            'symbol' => strtoupper($symbol),
        ])->json();
    }

    /**
     * Signed endpoint – get account info
     */
    public function getAccountInfo(): array
    {
        $timestamp = round(microtime(true) * 1000);
        $query     = http_build_query(['timestamp' => $timestamp]);
        $signature = hash_hmac('sha256', $query, $this->secretKey);

        return Http::withHeaders(['X-MBX-APIKEY' => $this->apiKey])
            ->get("{$this->baseUrl}/v3/account?{$query}&signature={$signature}")
            ->json();
    }

     public function getOpenOrders(): array
    {
        $timestamp = round(microtime(true)*1000);
        $query     = "timestamp=$timestamp";
        $sig = hash_hmac('sha256',$query,$this->secretKey);

        return Http::withHeaders(['X-MBX-APIKEY'=>$this->apiKey])
            ->get($this->baseUrl()."/v3/openOrders?$query&signature=$sig")
            ->json();
    }

    /**
     * Signed endpoint – place a market order
     */
    public function placeMarketOrder(string $symbol, string $side, string $quantity): array
    {
        $timestamp = round(microtime(true) * 1000);

        $params = [
            'symbol'    => strtoupper($symbol),
            'side'      => strtoupper($side),  // BUY or SELL
            'type'      => 'MARKET',
            'quantity'  => $quantity,
            'timestamp' => $timestamp,
        ];

        $query     = http_build_query($params);
        $signature = hash_hmac('sha256', $query, $this->secretKey);

        return Http::withHeaders(['X-MBX-APIKEY' => $this->apiKey])
            ->post("{$this->baseUrl}/v3/order?{$query}&signature={$signature}")
            ->json();
    }
}
