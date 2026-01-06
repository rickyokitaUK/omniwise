<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolanaTickBatch extends Model
{
    protected $table = 'solana_tick_batches';

    protected $fillable = [
        'minute',
        'ticks',
        'tick_count',
        'close',
        'ma4',
        'ma9',
    ];

    protected $casts = [
        'minute' => 'datetime',
        'ticks'  => 'array',   // auto-decode JSON
        'close'  => 'float',
        'ma4'    => 'float',
        'ma9'    => 'float',
    ];
}
