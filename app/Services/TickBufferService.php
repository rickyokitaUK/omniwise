<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\SolanaTickBatch;

class TickBufferService
{
    protected array $tickBuffer = [];
    protected int $currentMinute = -1;

    public function addTick(array $tick): void
    {
        $minute = (int) floor($tick['event_time'] / 60000); // minute epoch

        if ($this->currentMinute === -1) {
            $this->currentMinute = $minute;
        }

        if ($minute !== $this->currentMinute) {
            // flush previous minute + calculate MA4/MA9
            $this->flushMinute($this->currentMinute);
            $this->currentMinute = $minute;
        }

        $this->tickBuffer[$minute][] = $tick;
    }

    protected function flushMinute(int $minute)
    {
        if (!isset($this->tickBuffer[$minute])) return;

        $ticks = $this->tickBuffer[$minute];
        $count = count($ticks);

        if ($count === 0) {
            unset($this->tickBuffer[$minute]);
            return;
        }

        // Last tick price = close price of the minute
        $close = end($ticks)['price'];

        // Convert to actual datetime
        $minuteStart = date('Y-m-d H:i:00', $minute * 60);

        // ===== 1. Save tick batch first =====
        $id = DB::table('solana_tick_batches')->insertGetId([
            'minute'     => $minuteStart,
            'ticks'      => json_encode($ticks),
            'tick_count' => $count,
            'close'      => $close,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ===== 2. Compute MA4 / MA9 using last N closes =====

        $batches = SolanaTickBatch::orderBy('minute', 'desc')
            ->limit(9)
            ->pluck('close')
            ->toArray();

        // Most recent close is first, so reverse the array
        $batches = array_reverse($batches);

        // Calculate MAs
        $ma4 = count($batches) >= 4 ? array_sum(array_slice($batches, -4)) / 4 : null;
        $ma9 = count($batches) >= 9 ? array_sum(array_slice($batches, -9)) / 9 : null;

        // Update the last row with MA values
        DB::table('solana_tick_batches')
            ->where('id', $id)
            ->update([
                'ma4' => $ma4,
                'ma9' => $ma9,
            ]);

        unset($this->tickBuffer[$minute]);
    }

    public function __destruct()
    {
        if ($this->currentMinute !== -1) {
            $this->flushMinute($this->currentMinute);
        }
    }
}
