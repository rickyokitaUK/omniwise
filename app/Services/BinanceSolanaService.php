<?php
// app/Services/BinanceSolanaService.php

namespace App\Services;

use App\Models\SolanaKline;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class BinanceSolanaService
{
    const BASE_URL = 'https://api.binance.com/api/v3';

    /**
     * 取最近 N 個 1m K 線，for preload / backfill
     */
    public function fetchRecentKlines(int $limit = 100): array
    {
        $response = Http::get(self::BASE_URL . '/klines', [
            'symbol'   => 'SOLUSDT',
            'interval' => '1m',
            'limit'    => $limit,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Binance API failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * 把 Binance raw kline -> SolanaKline DB（upsert）
     */
    public function upsertKlineFromArray(array $klineData): SolanaKline
    {
        // Binance kline array:
        // 0 open time (ms), 1 open, 2 high, 3 low, 4 close, 5 volume, ...
        [$openTime, $open, $high, $low, $close, $volume] = [
            $klineData[0], $klineData[1], $klineData[2],
            $klineData[3], $klineData[4], $klineData[5] ?? 0,
        ];

        $openTimeCarbon = Carbon::createFromTimestampMs($openTime);

        $kline = SolanaKline::updateOrCreate(
            ['open_time' => $openTimeCarbon],
            [
                'open'   => $open,
                'high'   => $high,
                'low'    => $low,
                'close'  => $close,
                'volume' => $volume,
            ]
        );

        // 每次 upsert 完，更新 MA
        $this->updateMovingAveragesFor($kline);

        return $kline;
    }

    /**
     * 當 candle 完成時用（WebSocket 會 call 呢個）
     * Binance kline event 嘅 k 字段格式唔同於 klines REST
     */
    public function upsertFromWsKline(array $k): SolanaKline
    {
        // kline message example:
        // t: start time, o: open, h: high, l: low, c: close, v: volume, x: is this kline closed
        $openTime  = $k['t'];
        $open      = $k['o'];
        $high      = $k['h'];
        $low       = $k['l'];
        $close     = $k['c'];
        $volume    = $k['v'];

        $openTimeCarbon = Carbon::createFromTimestampMs($openTime);

        $kline = SolanaKline::updateOrCreate(
            ['open_time' => $openTimeCarbon],
            [
                'open'   => $open,
                'high'   => $high,
                'low'    => $low,
                'close'  => $close,
                'volume' => $volume,
            ]
        );

        $this->updateMovingAveragesFor($kline);

        return $kline;
    }

    /**
     * 更新某一條 kline 嘅 MA4 / MA9
     */
    public function updateMovingAveragesFor(SolanaKline $kline): void
    {
        $openTime = $kline->open_time;

        // 取由最舊到最新，直到 current candle
        $last4 = SolanaKline::where('open_time', '<=', $openTime)
            ->orderByDesc('open_time')
            ->take(4)
            ->pluck('close')
            ->toArray();

        $last9 = SolanaKline::where('open_time', '<=', $openTime)
            ->orderByDesc('open_time')
            ->take(9)
            ->pluck('close')
            ->toArray();

        $ma4 = count($last4) === 4 ? array_sum($last4) / 4 : null;
        $ma9 = count($last9) === 9 ? array_sum($last9) / 9 : null;

        $kline->ma4 = $ma4;
        $kline->ma9 = $ma9;
        $kline->save();
    }

    /**
     * 一個 helper，用 REST preload 整個歷史，for 初次 setup
     */
    public function backfillRecent(int $limit = 200): void
    {
        $rawKlines = $this->fetchRecentKlines($limit);

        foreach ($rawKlines as $k) {
            $this->upsertKlineFromArray($k);
        }
    }
}

