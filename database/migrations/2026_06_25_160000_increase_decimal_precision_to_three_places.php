<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('
            ALTER TABLE combustibil
                MODIFY kilometers DECIMAL(10, 3) NULL,
                MODIFY liters DECIMAL(10, 3) NULL,
                MODIFY total_price DECIMAL(10, 3) NULL,
                MODIFY price_per_liter DECIMAL(10, 3) NULL,
                MODIFY total_kilometers DECIMAL(10, 3) NULL
        ');

        DB::statement('
            ALTER TABLE ulei
                MODIFY total_kilometers DECIMAL(10, 3) NULL,
                MODIFY oil_amount DECIMAL(10, 3) NULL
        ');

        DB::statement('
            ALTER TABLE events
                MODIFY kilometers DECIMAL(10, 3) NULL
        ');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('
            ALTER TABLE combustibil
                MODIFY kilometers DECIMAL(10, 1) NULL,
                MODIFY liters DECIMAL(10, 1) NULL,
                MODIFY total_price DECIMAL(10, 1) NULL,
                MODIFY price_per_liter DECIMAL(10, 1) NULL,
                MODIFY total_kilometers DECIMAL(10, 1) NULL
        ');

        DB::statement('
            ALTER TABLE ulei
                MODIFY total_kilometers DECIMAL(10, 1) NULL,
                MODIFY oil_amount DECIMAL(10, 1) NULL
        ');

        DB::statement('
            ALTER TABLE events
                MODIFY kilometers DECIMAL(10, 1) NULL
        ');
    }
};
