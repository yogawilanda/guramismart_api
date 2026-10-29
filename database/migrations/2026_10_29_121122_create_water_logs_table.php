<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('water_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained()->cascadeOnDelete();
            $table->date('recorded_on');
            $table->decimal('temperature_c', 4, 1);
            $table->decimal('ph', 3, 1);
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['cycle_id', 'recorded_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_logs');
    }
};
