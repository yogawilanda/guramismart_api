<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->decimal('pond_area_m2', 10, 2);
            $table->unsignedInteger('seed_count');
            $table->string('seed_size')->nullable();
            $table->date('stocking_date');
            $table->unsignedBigInteger('initial_capital');
            $table->unsignedSmallInteger('target_days');
            $table->enum('status', ['active', 'harvested'])->default('active');
            $table->timestamp('harvest_notified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cycles');
    }
};
