<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_solana_klines_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('solana_klines', function (Blueprint $table) {
            $table->id();
            $table->dateTime('open_time')->index();
            $table->decimal('open', 18, 8);
            $table->decimal('high', 18, 8);
            $table->decimal('low', 18, 8);
            $table->decimal('close', 18, 8);
            $table->decimal('volume', 18, 8)->nullable();

            $table->decimal('ma4', 18, 8)->nullable();
            $table->decimal('ma9', 18, 8)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solana_klines');
    }
};
