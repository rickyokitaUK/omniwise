<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolanaKline extends Model
{
    protected $fillable = [
        'open_time', 'open', 'high', 'low', 'close', 'volume',
        'ma4', 'ma9',
    ];

    protected $casts = [
        'open_time' => 'datetime',
        'open'  => 'float',
        'high'  => 'float',
        'low'   => 'float',
        'close' => 'float',
        'volume'=> 'float',
        'ma4'   => 'float',
        'ma9'   => 'float',
    ];
}