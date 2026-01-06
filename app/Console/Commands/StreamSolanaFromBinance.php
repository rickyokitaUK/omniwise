<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TickBufferService;
use WebSocket\Client;
use Illuminate\Support\Facades\Log;

class StreamSolanaFromBinance extends Command
{
    protected $signature = 'solana:stream';
    protected $description = 'Stream SOLUSDT trades and store ticks in 1-min batches';

    public function handle(TickBufferService $ticks): int
    {
        $url = 'wss://stream.binance.com:9443/ws/solusdt@trade';

        $this->info("Connecting to Binance WebSocket: {$url}");

        $client = new Client($url, [
            'timeout' => 60,
        ]);

        while (true) {
            try {
                $msg = $client->receive();
                $data = json_decode($msg, true);

                if (!$data) {
                    continue;
                }

                // Binance trade stream has fields like p (price), q (qty), E (event time ms)
                if (!isset($data['p'], $data['q'], $data['E'])) {
                    continue;
                }

                $tick = [
                    'price'      => (float) $data['p'],
                    'qty'        => (float) $data['q'],
                    'event_time' => (int) $data['E'], // ms
                ];

                $ticks->addTick($tick);

            } catch (\Throwable $e) {
                Log::error('Solana tick stream error: ' . $e->getMessage());
                $this->error('Error: ' . $e->getMessage());

                sleep(2); // slight backoff
                $client = new Client($url, ['timeout' => 60]); // reconnect
            }
        }

        return Command::SUCCESS;
    }
}
