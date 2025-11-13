<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BinanceIndicatorService
{
    public function fetchKlines(string $symbol, string $interval = '1m', int $limit = 50, bool $testnet = false): array
    {
        $base = $testnet ? 'https://testnet.binance.vision' : 'https://api.binance.com';
        $url  = $base . '/api/v3/klines';
        $res = Http::get($url, ['symbol' => strtoupper($symbol), 'interval' => $interval, 'limit' => $limit]);

        if (!$res->ok()) {
            throw new \RuntimeException('Binance klines error: '.$res->body());
        }
        return $res->json();
    }

    public function indicators(string $symbol, string $interval = '1m', int $limit = 50, bool $testnet = false): array
    {
        $klines = $this->fetchKlines($symbol, $interval, $limit, $testnet);
        // kline: [ openTime, open, high, low, close, volume, closeTime, ... ]
        $closes = array_map(fn($k) => (float)$k[4], $klines);
        $last   = end($closes) ?: null;

        $ma = function(array $arr, int $n) {
            if (count($arr) < $n) return null;
            return array_sum(array_slice($arr, -$n)) / $n;
        };

        return [
            'symbol'  => strtoupper($symbol),
            'interval'=> $interval,
            'limit'   => $limit,
            'Nominal' => $last,
            'MA4'     => $ma($closes, 4),
            'MA9'     => $ma($closes, 9),
            'closes'  => $closes,
        ];
    }
}
