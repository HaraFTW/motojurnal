<?php

namespace Tests\Feature;

use App\Models\Ulei;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OilChangeToastTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_toast_when_over_5000_km_since_last_oil_change(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        Ulei::query()->create([
            'user_id' => $user->id,
            'total_kilometers' => 10000,
        ]);

        $user->forceFill(['kilometers' => 15001])->save();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('id="reminder-toast-stack"', false)
            ->assertSee('Probabil au trecut 5000km de la ultimul schimb de ulei', false);
    }

    public function test_shows_toast_when_over_one_year_since_last_oil_change(): void
    {
        $this->travelTo('2026-07-15');

        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        $oil = Ulei::query()->create([
            'user_id' => $user->id,
            'total_kilometers' => 10000,
        ]);

        $oil->forceFill([
            'created_at' => now()->subYear()->subDay(),
            'updated_at' => now()->subYear()->subDay(),
        ])->save();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Probabil a trecut un an de la ultimul schimb de ulei', false);
    }

    public function test_oil_change_toast_persists_across_pages_until_dismissed(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        Ulei::query()->create([
            'user_id' => $user->id,
            'total_kilometers' => 10000,
        ]);

        $user->forceFill(['kilometers' => 16000])->save();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Probabil au trecut 5000km de la ultimul schimb de ulei', false);

        $this->get('/ulei')
            ->assertOk()
            ->assertSee('Probabil au trecut 5000km de la ultimul schimb de ulei', false);

        $this->post(route('oil-change-toasts.dismiss'))
            ->assertNoContent();

        $this->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Probabil au trecut 5000km de la ultimul schimb de ulei', false);

        $this->get('/combustibil')
            ->assertOk()
            ->assertDontSee('Probabil au trecut 5000km de la ultimul schimb de ulei', false);
    }

    public function test_login_resets_dismissed_oil_toast(): void
    {
        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        Ulei::query()->create([
            'user_id' => $user->id,
            'total_kilometers' => 10000,
        ]);

        $user->forceFill(['kilometers' => 16000])->save();

        $this->actingAs($user)
            ->withSession(['oil_change_toasts_dismissed' => true])
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Probabil au trecut 5000km de la ultimul schimb de ulei', false);

        $this->post('/logout')->assertRedirect(route('login'));

        $response = $this->post('/login', [
            'plate_number' => 'B123ABC',
        ])->assertRedirect(route('dashboard'));

        $this->followRedirects($response)
            ->assertOk()
            ->assertSee('Probabil au trecut 5000km de la ultimul schimb de ulei', false);
    }

    public function test_no_oil_toast_when_under_thresholds(): void
    {
        $this->travelTo('2026-07-15');

        $user = User::factory()->create(['plate_number' => 'B123ABC']);

        Ulei::query()->create([
            'user_id' => $user->id,
            'total_kilometers' => 10000,
        ]);

        $user->forceFill(['kilometers' => 14000])->save();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Probabil au trecut 5000km de la ultimul schimb de ulei', false)
            ->assertDontSee('Probabil a trecut un an de la ultimul schimb de ulei', false);
    }
}
