<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipuri_ulei', function (Blueprint $table) {
            $table->unsignedInteger('priority')->default(0)->after('oil_type');
        });

        DB::statement('UPDATE tipuri_ulei SET priority = id');
    }

    public function down(): void
    {
        Schema::table('tipuri_ulei', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
