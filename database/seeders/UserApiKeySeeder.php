<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Models\UserApiKey;

class UserApiKeySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get a user to attach keys to
        //    (adjust query or create a user if needed)
        $user = DB::table('omni_user')
            ->where('username', 'Ricky Chow')
            ->first();

        if (! $user) {
            $this->command->warn('No users found - create a user first.');
            return;
        }

        // 2. Your Binance API credentials for the POC
        $testApiKey     = 'dZqCvjROzDUM3B5Bmb8FRa7wFYxYfaTMnNJTQQ3BfZMebbHLccUnJBcNihQla39e';
        $testSecretKey  = '5eblfJD9TxJh3hVimdBKGoCCeC6N9mp67XdMRpsayuMT6uWB5bq3qSe8ExDo0Fb7';

        $liveApiKey     = 'S2ZRGLwlgYRlZeJsekpgsgD10AJT1qwE9G8YO4G1emHje5k1muBkp1dCuDaJZ7MY';
        $liveSecretKey  = 'FzqazehwHVupKzX58nTMtNzKHcp74bFP6NIKfijqYlgSnUPMdjoSuwjhBJUJMsC1';

        $serverIp = '217.14.180.18';


        // 3. Create the encrypted record
        UserApiKey::updateOrCreate(
            [
                'user_id'   => $user->userid,
                'exchange'  => 'binance',
                'use_testnet' => true,
            ],
            [
                'api_key'    => $testApiKey,
                'secret_key' => Crypt::encryptString($testSecretKey),
                'ip_address' => $serverIp,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        UserApiKey::updateOrCreate(
            [
                'user_id'   => $user->userid,
                'exchange'  => 'binance',
                'use_testnet' => false,
            ],
            [
                'api_key'    => $liveApiKey,
                'secret_key' => Crypt::encryptString($liveSecretKey),
                'ip_address' => $serverIp,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );


        $this->command->info('Binance API keys seeded for user: '.$user->user_email);
    }
}
