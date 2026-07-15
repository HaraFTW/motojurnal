<?php

namespace Tests\Feature;

use App\Models\Reminder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_reminders_page(): void
    {
        $this->get('/reminders')->assertRedirect('/login');
    }

    public function test_reminders_page_lists_active_user_reminders(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        Reminder::query()->create([
            'user_id' => $user->id,
            'type' => 'RCA',
            'starting_date' => '2024-01-15',
            'ending_date' => '2025-01-15',
            'observations' => 'Polita activa',
        ]);

        Reminder::query()->create([
            'user_id' => $user->id,
            'type' => 'ITP',
            'starting_date' => '2024-01-15',
            'ending_date' => '2026-01-15',
            'active' => false,
        ]);

        $response = $this->actingAs($user)->get('/reminders');

        $response->assertOk();
        $response->assertSee('RCA', false);
        $response->assertSee('15.01.2024', false);
        $response->assertSee('15.01.2025', false);
        $response->assertSee('Polita activa', false);
        $response->assertSee('Adaugă reminder nou', false);
        $response->assertSee('Marchează ca rezolvat', false);

        $this->assertDatabaseCount('reminders', 2);
        $this->assertEquals(1, Reminder::query()->where('active', true)->count());
    }

    public function test_user_can_store_reminder(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $response = $this->actingAs($user)->post('/reminders', [
            'type' => 'ITP',
            'starting_date' => '2024-03-15',
            'ending_date' => '2026-03-15',
            'observations' => 'Verificare tehnica',
        ]);

        $response->assertRedirect(route('reminders.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reminders', [
            'user_id' => $user->id,
            'type' => 'ITP',
            'starting_date' => '2024-03-15 00:00:00',
            'ending_date' => '2026-03-15 00:00:00',
            'observations' => 'Verificare tehnica',
            'active' => true,
            'solved' => false,
        ]);
    }

    public function test_user_can_store_reminder_with_custom_type(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $response = $this->actingAs($user)->post('/reminders', [
            'type' => 'Altele',
            'custom_type' => 'Asigurare CASCO',
            'starting_date' => '2024-03-15',
            'ending_date' => '2025-03-15',
        ]);

        $response->assertRedirect(route('reminders.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reminders', [
            'user_id' => $user->id,
            'type' => 'Altele',
            'custom_type' => 'Asigurare CASCO',
        ]);
    }

    public function test_custom_type_is_required_when_type_is_altele(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $response = $this->actingAs($user)->post('/reminders', [
            'type' => 'Altele',
            'starting_date' => '2024-03-15',
        ]);

        $response->assertSessionHasErrors('custom_type');
    }

    public function test_type_is_required(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $response = $this->actingAs($user)->post('/reminders', [
            'starting_date' => '2024-03-15',
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_ending_date_must_be_on_or_after_starting_date(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $response = $this->actingAs($user)->post('/reminders', [
            'type' => 'RCA',
            'starting_date' => '2024-06-01',
            'ending_date' => '2024-05-01',
        ]);

        $response->assertSessionHasErrors('ending_date');
    }

    public function test_user_can_toggle_reminder_solved_state(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $reminder = Reminder::query()->create([
            'user_id' => $user->id,
            'type' => 'RCA',
            'ending_date' => now()->addDays(10)->toDateString(),
        ]);

        $this->actingAs($user)
            ->patch(route('reminders.toggle-solved', $reminder))
            ->assertRedirect(route('reminders.index'));

        $this->assertTrue($reminder->fresh()->solved);

        $this->actingAs($user)
            ->patch(route('reminders.toggle-solved', $reminder))
            ->assertRedirect(route('reminders.index'));

        $this->assertFalse($reminder->fresh()->solved);
    }

    public function test_user_can_soft_delete_reminder(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $reminder = Reminder::query()->create([
            'user_id' => $user->id,
            'type' => 'RCA',
            'ending_date' => now()->addDays(10)->toDateString(),
        ]);

        $this->actingAs($user)
            ->delete(route('reminders.destroy', $reminder))
            ->assertRedirect(route('reminders.index'));

        $this->assertFalse($reminder->fresh()->active);

        $this->actingAs($user)
            ->get('/reminders')
            ->assertOk()
            ->assertSee('Nu ai remindere salvate', false);
    }

    public function test_expiring_reminders_show_combined_toast_on_authenticated_pages(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $this->travelTo('2026-07-01');

        Reminder::query()->create([
            'user_id' => $user->id,
            'type' => 'RCA',
            'ending_date' => '2026-07-15',
        ]);

        Reminder::query()->create([
            'user_id' => $user->id,
            'type' => 'ITP',
            'ending_date' => '2026-07-10',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('id="reminder-toast-stack"', false);
        $response->assertSee('Asigurarea RCA expiră în 14 zile (15.07.2026)', false);
        $response->assertSee('ITP-ul expiră în 9 zile (10.07.2026)', false);
        $response->assertSee('"expired":false', false);
    }

    public function test_recently_expired_reminders_show_in_toast(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $this->travelTo('2026-07-15');

        Reminder::query()->create([
            'user_id' => $user->id,
            'type' => 'RCA',
            'ending_date' => '2026-07-14',
        ]);

        Reminder::query()->create([
            'user_id' => $user->id,
            'type' => 'ITP',
            'ending_date' => '2026-07-10',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Asigurarea RCA a expirat ieri (14.07.2026)', false);
        $response->assertSee('ITP-ul a expirat de 5 zile (10.07.2026)', false);
        $response->assertSee('"expired":true', false);
    }

    public function test_expired_reminders_older_than_20_days_are_not_shown_in_toast(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $this->travelTo('2026-07-15');

        Reminder::query()->create([
            'user_id' => $user->id,
            'type' => 'RCA',
            'ending_date' => '2026-06-20',
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Asigurarea RCA a expirat', false);
    }

    public function test_expiring_reminders_do_not_show_toast_on_reminders_page(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $this->travelTo('2026-07-01');

        Reminder::query()->create([
            'user_id' => $user->id,
            'type' => 'RCA',
            'ending_date' => '2026-07-15',
        ]);

        $this->actingAs($user)
            ->get('/reminders')
            ->assertOk()
            ->assertDontSee('reminder-toast-stack', false)
            ->assertDontSee('Asigurarea RCA expiră', false);
    }

    public function test_solved_reminders_are_not_shown_in_toast(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $this->travelTo('2026-07-01');

        Reminder::query()->create([
            'user_id' => $user->id,
            'type' => 'RCA',
            'ending_date' => '2026-07-15',
            'solved' => true,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Asigurarea RCA expiră', false);
    }

    public function test_inactive_reminders_are_not_shown_in_toast(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $this->travelTo('2026-07-01');

        Reminder::query()->create([
            'user_id' => $user->id,
            'type' => 'RCA',
            'ending_date' => '2026-07-15',
            'active' => false,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Asigurarea RCA expiră', false);
    }

    public function test_expiration_toast_messages_for_each_type(): void
    {
        $this->travelTo('2026-07-01');

        $reminder = Reminder::query()->make([
            'type' => 'ITP',
            'ending_date' => '2026-07-10',
        ]);

        $this->assertSame(
            'ITP-ul expiră în 9 zile (10.07.2026)',
            $reminder->expirationToastMessage(),
        );

        $reminder = Reminder::query()->make([
            'type' => 'Rovinieta',
            'ending_date' => '2026-07-10',
        ]);

        $this->assertSame(
            'Rovinieta expiră în 9 zile (10.07.2026)',
            $reminder->expirationToastMessage(),
        );

        $reminder = Reminder::query()->make([
            'type' => 'Altele',
            'custom_type' => 'CASCO',
            'ending_date' => '2026-07-10',
        ]);

        $this->assertSame(
            'Documentul CASCO expiră în 9 zile (10.07.2026)',
            $reminder->expirationToastMessage(),
        );
    }

    public function test_expiration_toast_message_uses_azi_when_ending_date_is_today(): void
    {
        $this->travelTo('2026-07-15');

        $reminder = Reminder::query()->make([
            'type' => 'RCA',
            'ending_date' => '2026-07-15',
        ]);

        $this->assertSame(
            'Asigurarea RCA expiră azi (15.07.2026)',
            $reminder->expirationToastMessage(),
        );
    }

    public function test_expiration_toast_message_uses_maine_when_ending_date_is_tomorrow(): void
    {
        $this->travelTo('2026-07-15');

        $reminder = Reminder::query()->make([
            'type' => 'RCA',
            'ending_date' => '2026-07-16',
        ]);

        $this->assertSame(
            'Asigurarea RCA expiră mâine (16.07.2026)',
            $reminder->expirationToastMessage(),
        );
    }

    public function test_expired_toast_messages(): void
    {
        $this->travelTo('2026-07-15');

        $reminder = Reminder::query()->make([
            'type' => 'RCA',
            'ending_date' => '2026-07-14',
        ]);

        $this->assertSame(
            'Asigurarea RCA a expirat ieri (14.07.2026)',
            $reminder->expiredToastMessage(),
        );

        $reminder = Reminder::query()->make([
            'type' => 'Altele',
            'custom_type' => 'CASCO',
            'ending_date' => '2026-07-10',
        ]);

        $this->assertSame(
            'Documentul CASCO a expirat de 5 zile (10.07.2026)',
            $reminder->expiredToastMessage(),
        );
    }
}
