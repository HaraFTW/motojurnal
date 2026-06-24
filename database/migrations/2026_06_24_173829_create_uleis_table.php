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
        Schema::create('ulei', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_kilometers', 10, 1)->nullable();
            $table->boolean('oil_filter')->default(false);
            $table->boolean('gasket')->default(false);
            $table->decimal('oil_amount', 10, 1)->nullable();
            $table->string('oil_brand')->nullable();
            $table->foreignId('oil_type_id')->nullable()->constrained('tipuri_ulei')->nullOnDelete();
            $table->string('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ulei');
    }
};
