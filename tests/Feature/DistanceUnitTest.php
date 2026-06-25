<?php

namespace Tests\Feature;

use App\Enums\DistanceUnit;
use App\Models\User;
use App\Support\Distance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistanceUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_switch_distance_unit(): void
    {
        $user = User::factory()->create([
            'plate_number' => 'B123ABC',
            'distance_unit' => DistanceUnit::Km,
        ]);

        $response = $this->actingAs($user)->patch(route('distance-unit.update'), [
            'distance_unit' => 'mi',
        ]);

        $response->assertRedirect();
        $this->assertSame(DistanceUnit::Miles, $user->fresh()->distance_unit);
    }

    public function test_miles_user_fuel_entry_is_stored_as_kilometers(): void
    {
        $user = User::factory()->create([
            'plate_number' => 'B123ABC',
            'distance_unit' => DistanceUnit::Miles,
        ]);

        $miles = 100.0;
        $kilometers = round($miles * Distance::KM_PER_MILE, 1);

        $response = $this->actingAs($user)->post('/combustibil', [
            'kilometers' => (string) $miles,
            'liters' => '8.2',
        ]);

        $response->assertRedirect(route('fuel.index'));

        $this->assertDatabaseHas('combustibil', [
            'user_id' => $user->id,
            'kilometers' => (string) $kilometers,
            'liters' => '8.2',
        ]);
    }

    public function test_miles_user_sees_mile_labels_on_fuel_page(): void
    {
        $user = User::factory()->create([
            'plate_number' => 'B123ABC',
            'distance_unit' => DistanceUnit::Miles,
        ]);

        $response = $this->actingAs($user)->get('/combustibil');

        $response->assertOk();
        $response->assertSee('Total mile', false);
        $response->assertSee('for="kilometers"', false);
    }

    public function test_miles_user_history_still_displays_kilometers(): void
    {
        $user = User::factory()->create([
            'plate_number' => 'B123ABC',
            'distance_unit' => DistanceUnit::Miles,
        ]);

        $user->combustibil()->create([
            'kilometers' => 160.9,
            'liters' => 8.0,
            'total_kilometers' => 45230.0,
        ]);

        $response = $this->actingAs($user)->get('/combustibil');

        $response->assertOk();
        $response->assertSee('160.9', false);
        $response->assertSee('45230.0', false);
        $response->assertSee('Kilometri', false);
        $response->assertSee('Total km', false);
    }

    public function test_new_users_default_to_kilometers(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $this->assertSame(DistanceUnit::Km, $user->distance_unit);
    }
}
