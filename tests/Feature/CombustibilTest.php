<?php

namespace Tests\Feature;

use App\Models\Combustibil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CombustibilTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_combustibil_page(): void
    {
        $this->get('/combustibil')->assertRedirect('/login');
    }

    public function test_user_can_store_combustibil_entry(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $response = $this->actingAs($user)->post('/combustibil', [
            'kilometers' => '120.5',
            'liters' => '8.2',
            'total_price' => '65.4',
            'price_per_liter' => '7.9',
            'total_kilometers' => '45230.0',
            'observations' => 'Plin rezervor',
        ]);

        $response->assertRedirect(route('fuel.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('combustibil', [
            'user_id' => $user->id,
            'kilometers' => '120.5',
            'liters' => '8.2',
            'total_price' => '65.4',
            'price_per_liter' => '7.9',
            'total_kilometers' => '45230.0',
            'observations' => 'Plin rezervor',
        ]);
    }

    public function test_kilometers_and_liters_are_required(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $response = $this->actingAs($user)->post('/combustibil', []);

        $response->assertSessionHasErrors([
            'kilometers' => 'Câmpul kilometri este obligatoriu.',
            'liters' => 'Câmpul litri combustibil este obligatoriu.',
        ]);
        $this->assertDatabaseCount('combustibil', 0);
    }

    public function test_user_only_sees_own_combustibil_entries(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $other = User::factory()->create(['plate_number' => 'C999XYZ']);

        Combustibil::create(['user_id' => $user->id, 'liters' => 10.0]);
        Combustibil::create(['user_id' => $other->id, 'liters' => 20.0]);

        $response = $this->actingAs($user)->get('/combustibil');

        $response->assertOk();
        $response->assertSee('10.0', false);
        $response->assertDontSee('20.0', false);
        $response->assertSee('id="fuel-history-open"', false);
    }

    public function test_fuel_chart_button_appears_when_consumption_can_be_calculated(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        Combustibil::create([
            'user_id' => $user->id,
            'kilometers' => '100.0',
            'liters' => '6.5',
        ]);

        $response = $this->actingAs($user)->get('/combustibil');

        $response->assertOk();
        $response->assertSee('id="fuel-chart-open"', false);
        $response->assertSee('"consumption":6.5', false);
    }

    public function test_fuel_chart_button_hidden_without_complete_entries(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        Combustibil::create([
            'user_id' => $user->id,
            'kilometers' => null,
            'liters' => '6.5',
        ]);

        $response = $this->actingAs($user)->get('/combustibil');

        $response->assertOk();
        $response->assertDontSee('id="fuel-chart-open"', false);
    }

    public function test_user_can_update_own_combustibil_entry(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $entry = Combustibil::create([
            'user_id' => $user->id,
            'kilometers' => '100.0',
            'liters' => '5.0',
        ]);

        $response = $this->actingAs($user)->put(route('fuel.update', $entry), [
            'kilometers' => '150.0',
            'liters' => '7.5',
        ]);

        $response->assertRedirect(route('fuel.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('combustibil', [
            'id' => $entry->id,
            'kilometers' => '150.0',
            'liters' => '7.5',
        ]);
    }

    public function test_user_can_delete_own_combustibil_entry(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $entry = Combustibil::create([
            'user_id' => $user->id,
            'kilometers' => '100.0',
            'liters' => '5.0',
        ]);

        $response = $this->actingAs($user)->delete(route('fuel.destroy', $entry));

        $response->assertRedirect(route('fuel.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('combustibil', ['id' => $entry->id]);
    }

    public function test_user_cannot_modify_another_users_combustibil_entry(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $other = User::factory()->create(['plate_number' => 'C999XYZ']);
        $entry = Combustibil::create([
            'user_id' => $other->id,
            'kilometers' => '100.0',
            'liters' => '5.0',
        ]);

        $this->actingAs($user)->put(route('fuel.update', $entry), [
            'kilometers' => '150.0',
            'liters' => '7.5',
        ])->assertForbidden();

        $this->actingAs($user)->delete(route('fuel.destroy', $entry))->assertForbidden();
    }
}
