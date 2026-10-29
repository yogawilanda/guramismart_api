<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained()->cascadeOnDelete();
            $table->date('fed_on');
            $table->enum('session', ['pagi', 'sore'])->nullable();
            $table->decimal('amount_kg', 10, 2);
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['cycle_id', 'fed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_logs');
    }
};
