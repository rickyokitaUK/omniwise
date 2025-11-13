<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BinanceIndicatorService;
use App\Services\AlgoPolicyService;

class AlgoController extends Controller
{
    public function __construct(
        private BinanceIndicatorService $indSvc,
        private AlgoPolicyService $algoSvc
    ) {}

    public function decide(Request $req)
    {
        $data = $req->validate([
            'symbol'   => ['required','string'],
            'interval' => ['nullable','string'], // default 1m
            'limit'    => ['nullable','integer','between:10,200'],
            'testnet'  => ['nullable','boolean'],
            'policy'   => ['required','string'], // DSL 規則
        ]);

        $ind = $this->indSvc->indicators(
            $data['symbol'],
            $data['interval'] ?? '1m',
            $data['limit'] ?? 50,
            (bool)($data['testnet'] ?? false)
        );

        $action = $this->algoSvc->decide($ind, $data['policy']);

        return response()->json([
            'symbol'    => strtoupper($data['symbol']),
            'interval'  => $data['interval'] ?? '1m',
            'Nominal'   => $ind['Nominal'],
            'MA4'       => $ind['MA4'],
            'MA9'       => $ind['MA9'],
            'decision'  => $action, // BUY | SELL | HOLD
            'evaluated' => now()->toIso8601String(),
        ]);
    }
}
