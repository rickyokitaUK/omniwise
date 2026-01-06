<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('solana_tick_batches', function (Blueprint $table) {
            $table->id();
            $table->dateTime('minute')->index();  // e.g. 2025-12-14 20:32
            $table->json('ticks');               // array of tick data
            $table->integer('tick_count');
            
            $table->decimal('close', 18, 8)->nullable();
            $table->decimal('ma4', 18, 8)->nullable();
            $table->decimal('ma9', 18, 8)->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solana_tick_batches');
    }
};
