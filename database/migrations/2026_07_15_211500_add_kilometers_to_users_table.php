<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('kilometers', 10, 3)->nullable()->after('distance_unit');
        });

        $users = DB::table('users')->select('id')->get();

        foreach ($users as $user) {
            $candidates = [
                DB::table('combustibil')->where('user_id', $user->id)->max('total_kilometers'),
                DB::table('events')->where('user_id', $user->id)->max('kilometers'),
                DB::table('ulei')->where('user_id', $user->id)->max('total_kilometers'),
            ];

            $max = collect($candidates)
                ->filter(fn ($value) => $value !== null)
                ->map(fn ($value) => (float) $value)
                ->max();

            if ($max !== null) {
                DB::table('users')->where('id', $user->id)->update([
                    'kilometers' => $max,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('kilometers');
        });
    }
};
