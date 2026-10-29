<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('harvest_date');
            $table->decimal('total_weight_kg', 10, 2);
            $table->unsignedBigInteger('total_sales');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};
