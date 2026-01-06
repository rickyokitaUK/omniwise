<?php
// app/Http/Controllers/Api/SolanaController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolanaKline;
use Illuminate\Http\Request;

class SolanaController extends Controller
{
    public function latest()
    {
        $latest = SolanaKline::orderByDesc('open_time')->firstOrFail();

        return response()->json([
            'open_time' => $latest->open_time,
            'open'      => $latest->open,
            'high'      => $latest->high,
            'low'       => $latest->low,
            'close'     => $latest->close,
            'volume'    => $latest->volume,
            'ma4'       => $latest->ma4,
            'ma9'       => $latest->ma9,
        ]);
    }

    public function history(Request $request)
    {
        $limit = (int) $request->get('limit', 200);

        $klines = SolanaKline::orderByDesc('open_time')
            ->limit($limit)
            ->get()
            ->sortBy('open_time')
            ->values()
            ->map(function ($k) {
                return [
                    'open_time' => $k->open_time,
                    'open'      => $k->open,
                    'high'      => $k->high,
                    'low'       => $k->low,
                    'close'     => $k->close,
                    'volume'    => $k->volume,
                    'ma4'       => $k->ma4,
                    'ma9'       => $k->ma9,
                ];
            });

        return response()->json($klines);
    }
}
