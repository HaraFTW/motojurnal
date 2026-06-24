<?php

namespace Tests\Unit;

use App\Models\Combustibil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CombustibilTest extends TestCase
{
    use RefreshDatabase;

    public function test_fuel_consumption_is_liters_per_100_kilometers(): void
    {
        $entry = new Combustibil([
            'kilometers' => '120.5',
            'liters' => '8.2',
        ]);

        $this->assertSame(6.8, $entry->fuelConsumptionPer100Km());
    }

    public function test_fuel_consumption_returns_null_without_kilometers_or_liters(): void
    {
        $this->assertNull((new Combustibil(['liters' => '8.2']))->fuelConsumptionPer100Km());
        $this->assertNull((new Combustibil(['kilometers' => '100.0']))->fuelConsumptionPer100Km());
        $this->assertNull((new Combustibil(['kilometers' => '0.0', 'liters' => '8.2']))->fuelConsumptionPer100Km());
    }

    public function test_consumption_chart_data_only_includes_complete_entries_for_user(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $other = User::factory()->create(['plate_number' => 'C999XYZ']);

        Combustibil::create([
            'user_id' => $user->id,
            'kilometers' => '100.0',
            'liters' => '5.0',
            'created_at' => now()->subDays(2),
        ]);
        Combustibil::create([
            'user_id' => $user->id,
            'kilometers' => null,
            'liters' => '5.0',
            'created_at' => now()->subDay(),
        ]);
        Combustibil::create([
            'user_id' => $user->id,
            'kilometers' => '200.0',
            'liters' => '10.0',
            'created_at' => now(),
        ]);
        Combustibil::create([
            'user_id' => $other->id,
            'kilometers' => '50.0',
            'liters' => '10.0',
        ]);

        $chartData = Combustibil::consumptionChartDataForUser($user);

        $this->assertCount(2, $chartData);
        $this->assertSame(5.0, $chartData[0]['consumption']);
        $this->assertSame(5.0, $chartData[1]['consumption']);
    }
}
