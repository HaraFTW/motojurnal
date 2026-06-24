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
        Schema::create('combustibil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('kilometers', 10, 1)->nullable();
            $table->decimal('liters', 10, 1)->nullable();
            $table->decimal('total_price', 10, 1)->nullable();
            $table->decimal('price_per_liter', 10, 1)->nullable();
            $table->decimal('total_kilometers', 10, 1)->nullable();
            $table->string('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combustibil');
    }
};
