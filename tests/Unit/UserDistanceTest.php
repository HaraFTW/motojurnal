<?php

namespace Tests\Unit;

use App\Enums\DistanceUnit;
use App\Models\User;
use App\Support\Decimal;
use App\Support\Distance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDistanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_kilometers_user_keeps_values_unchanged(): void
    {
        $user = User::factory()->create([
            'plate_number' => 'B123ABC',
            'distance_unit' => DistanceUnit::Km,
        ]);

        $this->assertSame(120.5, $user->kmToDisplay(120.5));
        $this->assertSame(120.5, $user->displayToKm(120.5));
        $this->assertSame('120.500', $user->formatDistance(120.5));
        $this->assertSame('Kilometri', $user->distanceFieldLabel('kilometers'));
        $this->assertSame('Total kilometri', $user->distanceFieldLabel('total_kilometers'));
    }

    public function test_miles_user_converts_between_display_and_storage(): void
    {
        $user = User::factory()->create([
            'plate_number' => 'B123ABC',
            'distance_unit' => DistanceUnit::Miles,
        ]);

        $miles = 100.0;
        $kilometers = Decimal::round($miles * Distance::KM_PER_MILE);

        $this->assertSame($miles, $user->kmToDisplay($kilometers));
        $this->assertSame($kilometers, $user->displayToKm($miles));
        $this->assertSame('100.000', $user->formatDistance($kilometers));
        $this->assertSame('Mile', $user->distanceFieldLabel('kilometers'));
        $this->assertSame('Total mile', $user->distanceFieldLabel('total_kilometers'));
    }
}
