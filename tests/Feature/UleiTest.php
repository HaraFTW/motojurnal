<?php

namespace Tests\Feature;

use App\Models\TipUlei;
use App\Models\Ulei;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UleiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_ulei_page(): void
    {
        $this->get('/ulei')->assertRedirect('/login');
    }

    public function test_user_can_store_ulei_entry(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $oilType = TipUlei::query()->where('oil_type', '5W-30')->firstOrFail();

        $response = $this->actingAs($user)->post('/ulei', [
            'total_kilometers' => '45230.5',
            'oil_filter' => '1',
            'gasket' => '0',
            'oil_amount' => '3.7',
            'oil_brand' => 'Castrol',
            'oil_type_id' => (string) $oilType->id,
            'observations' => 'Schimb complet',
        ]);

        $response->assertRedirect(route('oil.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ulei', [
            'user_id' => $user->id,
            'total_kilometers' => '45230.5',
            'oil_filter' => true,
            'gasket' => false,
            'oil_amount' => '3.7',
            'oil_brand' => 'Castrol',
            'oil_type_id' => $oilType->id,
            'observations' => 'Schimb complet',
        ]);
    }

    public function test_total_kilometers_is_required(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $response = $this->actingAs($user)->post('/ulei', []);

        $response->assertSessionHasErrors([
            'total_kilometers' => 'Câmpul total kilometri este obligatoriu.',
        ]);
        $this->assertDatabaseCount('ulei', 0);
    }

    public function test_oil_page_lists_seeded_oil_types(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $response = $this->actingAs($user)->get('/ulei');

        $response->assertOk();
        $response->assertSee('5W-30', false);
        $response->assertSee('0W-12', false);
        $this->assertDatabaseCount('tipuri_ulei', 24);
    }

    public function test_oil_types_are_ordered_by_priority_in_select(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $first = TipUlei::query()->where('oil_type', '0W-20')->firstOrFail();
        $second = TipUlei::query()->where('oil_type', '5W-30')->firstOrFail();

        $first->update(['priority' => 2]);
        $second->update(['priority' => 1]);

        $response = $this->actingAs($user)->get('/ulei');

        $response->assertOk();
        $response->assertSeeInOrder(['5W-30', '0W-20'], false);
    }

    public function test_user_only_sees_own_ulei_entries(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $other = User::factory()->create(['plate_number' => 'C999XYZ']);

        Ulei::create(['user_id' => $user->id, 'total_kilometers' => 1000.0]);
        Ulei::create(['user_id' => $other->id, 'total_kilometers' => 2000.0]);

        $response = $this->actingAs($user)->get('/ulei');

        $response->assertOk();
        $response->assertSee('1000.000', false);
        $response->assertDontSee('2000.0', false);
        $response->assertSee('id="oil-history-open"', false);
    }

    public function test_user_can_update_own_ulei_entry(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $entry = Ulei::create([
            'user_id' => $user->id,
            'total_kilometers' => 1000.0,
        ]);

        $response = $this->actingAs($user)->put(route('oil.update', $entry), [
            'total_kilometers' => '2000.0',
            'oil_filter' => '1',
            'gasket' => '0',
        ]);

        $response->assertRedirect(route('oil.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('ulei', [
            'id' => $entry->id,
            'total_kilometers' => '2000.0',
            'oil_filter' => true,
            'gasket' => false,
        ]);
    }

    public function test_user_can_delete_own_ulei_entry(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $entry = Ulei::create([
            'user_id' => $user->id,
            'total_kilometers' => 1000.0,
        ]);

        $response = $this->actingAs($user)->delete(route('oil.destroy', $entry));

        $response->assertRedirect(route('oil.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('ulei', ['id' => $entry->id]);
    }

    public function test_user_cannot_modify_another_users_ulei_entry(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);
        $other = User::factory()->create(['plate_number' => 'C999XYZ']);
        $entry = Ulei::create([
            'user_id' => $other->id,
            'total_kilometers' => 1000.0,
        ]);

        $this->actingAs($user)->put(route('oil.update', $entry), [
            'total_kilometers' => '2000.0',
        ])->assertForbidden();

        $this->actingAs($user)->delete(route('oil.destroy', $entry))->assertForbidden();
    }
}
