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
        Schema::create('admin_files', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('stored_path');
            $table->text('extra')->nullable();
            $table->unsignedBigInteger('size');
            $table->string('mime_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_files');
    }
};
