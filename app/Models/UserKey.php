<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserKey extends Model
{
     protected $fillable = ['username','api_key_encrypted','secret_key_encrypted','use_testnet'];
}
