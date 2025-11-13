<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserApiKey extends Model
{
    protected $fillable = [
        'user_id','exchange','api_key','secret_key','use_testnet','ip_address'
    ];

    protected $hidden = [
        'secret_key', // hide when serializing to JSON
        'api_key',
    ];

     public function user()
    {
        return $this->belongsTo(OmniUser::class, 'user_id', 'userid');
    }
}
