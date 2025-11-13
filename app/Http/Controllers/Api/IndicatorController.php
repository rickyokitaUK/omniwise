<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BinanceIndicatorService;

class IndicatorController extends Controller
{
    public function __construct(private BinanceIndicatorService $svc) {}

    public function show(Request $req, string $symbol)
    {
        $interval = $req->query('interval','1m');
        $limit    = (int)$req->query('limit', 50);
        $testnet  = (bool)$req->query('testnet', false);

        return response()->json(
            $this->svc->indicators($symbol, $interval, $limit, $testnet)
        );
    }
}
