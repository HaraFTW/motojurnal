<?php

use App\Models\Combustibil;
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
        Schema::table('combustibil', function (Blueprint $table) {
            $table->decimal('consum', 10, 3)->nullable()->after('liters');
        });

        Combustibil::query()->each(function (Combustibil $entry): void {
            $entry->consum = Combustibil::calculateConsum($entry->kilometers, $entry->liters);
            $entry->saveQuietly();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('combustibil', function (Blueprint $table) {
            $table->dropColumn('consum');
        });
    }
};
