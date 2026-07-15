<?php

namespace Tests\Feature;

use App\Models\Combustibil;
use App\Models\Event;
use App\Models\EventType;
use App\Models\User;
use App\Models\Ulei;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserKilometersSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_create_updates_user_kilometers(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $eventType = EventType::query()->where('event_name', 'Altele')->firstOrFail();

        $this->actingAs($user)->post('/evenimente', [
            'event_type_id' => (string) $eventType->id,
            'kilometers' => '12345.5',
            'event_date' => '2024-03-15',
        ])->assertRedirect(route('events.index'));

        $this->assertSame('12345.500', $user->fresh()->kilometers);
    }

    public function test_ulei_create_updates_user_kilometers(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $this->actingAs($user)->post('/ulei', [
            'total_kilometers' => '15000.0',
            'oil_amount' => '3.5',
        ])->assertRedirect(route('oil.index'));

        $this->assertSame('15000.000', $user->fresh()->kilometers);
    }

    public function test_combustibil_create_with_total_kilometers_updates_user_kilometers(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $this->actingAs($user)->post('/combustibil', [
            'kilometers' => '120.5',
            'liters' => '8.0',
            'total_kilometers' => '45230.0',
        ])->assertRedirect(route('fuel.index'));

        $this->assertSame('45230.000', $user->fresh()->kilometers);
    }

    public function test_combustibil_create_without_total_kilometers_adds_trip_to_user_kilometers(): void
    {
        $user = User::factory()->create([
            'plate_number' => 'B123ABC',
            'kilometers' => 10000,
        ]);

        $this->actingAs($user)->post('/combustibil', [
            'kilometers' => '250.5',
            'liters' => '12.0',
        ])->assertRedirect(route('fuel.index'));

        $this->assertSame('10250.500', $user->fresh()->kilometers);
    }

    public function test_combustibil_create_without_total_kilometers_uses_trip_when_user_kilometers_is_null(): void
    {
        $user = User::factory()->create([
            'plate_number' => 'B123ABC',
            'kilometers' => null,
        ]);

        Combustibil::query()->create([
            'user_id' => $user->id,
            'kilometers' => '100.0',
            'liters' => '5.0',
        ]);

        $this->assertSame('100.000', $user->fresh()->kilometers);
    }

    public function test_later_odometer_reading_overwrites_user_kilometers(): void
    {
        $user = User::factory()->create([
            'plate_number' => 'B123ABC',
            'kilometers' => 10000,
        ]);

        Ulei::query()->create([
            'user_id' => $user->id,
            'total_kilometers' => '11200.5',
            'oil_filter' => false,
            'gasket' => false,
        ]);

        $this->assertSame('11200.500', $user->fresh()->kilometers);

        $eventType = EventType::query()->where('event_name', 'Altele')->firstOrFail();

        Event::query()->create([
            'user_id' => $user->id,
            'event_type_id' => $eventType->id,
            'kilometers' => '12000.0',
            'event_date' => '2024-03-15',
        ]);

        $this->assertSame('12000.000', $user->fresh()->kilometers);
    }
}
