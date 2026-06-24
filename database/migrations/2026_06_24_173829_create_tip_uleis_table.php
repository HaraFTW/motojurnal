<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $oilTypes = [
        '0W-20',
        '0W-8',
        '0W-40',
        '0W-12',
        '0W-16',
        '0W-30',
        '0W-50',
        '5W-30',
        '5W-50',
        '5W-20',
        '5W-40',
        '10W-60',
        '10W-30',
        '10W-40',
        '10W-50',
        '10W',
        '15W-50',
        '15W-40',
        '20W-20',
        '20W-50',
        '20W-60',
        '30',
        '40',
        '50',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tipuri_ulei', function (Blueprint $table) {
            $table->id();
            $table->string('oil_type');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('tipuri_ulei')->insert(array_map(
            fn (string $oilType) => [
                'oil_type' => $oilType,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $this->oilTypes,
        ));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipuri_ulei');
    }
};
