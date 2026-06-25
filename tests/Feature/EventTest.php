<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_events_page(): void
    {
        $this->get('/evenimente')->assertRedirect('/login');
    }

    public function test_events_page_lists_seeded_event_types(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $response = $this->actingAs($user)->get('/evenimente');

        $response->assertOk();
        $response->assertSee('Altele', false);
        $this->assertDatabaseHas('event_types', [
            'event_name' => 'Altele',
            'active' => true,
        ]);
    }

    public function test_user_can_store_event_entry(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $eventType = EventType::query()->where('event_name', 'Altele')->firstOrFail();

        $response = $this->actingAs($user)->post('/evenimente', [
            'event_type_id' => (string) $eventType->id,
            'kilometers' => '12345.5',
            'event_date' => '2024-03-15',
            'observations' => 'Revizie generala',
        ]);

        $response->assertRedirect(route('events.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('events', [
            'user_id' => $user->id,
            'event_type_id' => $eventType->id,
            'kilometers' => '12345.5',
            'event_date' => '2024-03-15 00:00:00',
            'observations' => 'Revizie generala',
        ]);
    }

    public function test_event_date_defaults_to_today_when_omitted(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $eventType = EventType::query()->where('event_name', 'Altele')->firstOrFail();

        $this->travelTo('2024-06-10 15:30:00');

        $response = $this->actingAs($user)->post('/evenimente', [
            'event_type_id' => (string) $eventType->id,
            'kilometers' => '1000.0',
        ]);

        $response->assertRedirect(route('events.index'));

        $this->assertDatabaseHas('events', [
            'user_id' => $user->id,
            'event_date' => '2024-06-10 00:00:00',
        ]);
    }

    public function test_kilometers_and_event_type_are_required(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $response = $this->actingAs($user)->post('/evenimente', []);

        $response->assertSessionHasErrors([
            'event_type_id' => 'Câmpul tip eveniment este obligatoriu.',
            'kilometers' => 'Câmpul kilometri este obligatoriu.',
        ]);
        $this->assertDatabaseCount('events', 0);
    }

    public function test_user_only_sees_own_event_entries(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $other = User::factory()->create(['plate_number' => 'C999XYZ']);
        $eventType = EventType::query()->where('event_name', 'Altele')->firstOrFail();

        Event::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType->id,
            'event_date' => '2024-01-01',
            'kilometers' => 1000.0,
        ]);
        Event::create([
            'user_id' => $other->id,
            'event_type_id' => $eventType->id,
            'event_date' => '2024-01-02',
            'kilometers' => 2000.0,
        ]);

        $response = $this->actingAs($user)->get('/evenimente');

        $response->assertOk();
        $response->assertSee('1000.0', false);
        $response->assertDontSee('2000.0', false);
        $response->assertSee('id="events-history-open"', false);
    }

    public function test_user_can_update_own_event_entry(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $eventType = EventType::query()->where('event_name', 'Altele')->firstOrFail();
        $entry = Event::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType->id,
            'event_date' => '2024-01-01',
            'kilometers' => 1000.0,
        ]);

        $response = $this->actingAs($user)->put(route('events.update', $entry), [
            'event_type_id' => (string) $eventType->id,
            'kilometers' => '2500.0',
            'event_date' => '2024-05-20',
            'observations' => 'Actualizat',
        ]);

        $response->assertRedirect(route('events.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('events', [
            'id' => $entry->id,
            'kilometers' => '2500.0',
            'event_date' => '2024-05-20 00:00:00',
            'observations' => 'Actualizat',
        ]);
    }

    public function test_user_can_delete_own_event_entry(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $eventType = EventType::query()->where('event_name', 'Altele')->firstOrFail();
        $entry = Event::create([
            'user_id' => $user->id,
            'event_type_id' => $eventType->id,
            'event_date' => '2024-01-01',
            'kilometers' => 1000.0,
        ]);

        $response = $this->actingAs($user)->delete(route('events.destroy', $entry));

        $response->assertRedirect(route('events.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('events', ['id' => $entry->id]);
    }

    public function test_user_cannot_modify_another_users_event_entry(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $other = User::factory()->create(['plate_number' => 'C999XYZ']);
        $eventType = EventType::query()->where('event_name', 'Altele')->firstOrFail();
        $entry = Event::create([
            'user_id' => $other->id,
            'event_type_id' => $eventType->id,
            'event_date' => '2024-01-01',
            'kilometers' => 1000.0,
        ]);

        $this->actingAs($user)->put(route('events.update', $entry), [
            'event_type_id' => (string) $eventType->id,
            'kilometers' => '2500.0',
        ])->assertForbidden();

        $this->actingAs($user)->delete(route('events.destroy', $entry))->assertForbidden();
    }
}
