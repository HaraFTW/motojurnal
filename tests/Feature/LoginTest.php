<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_lowercase_plate_is_stored_uppercase_on_login(): void
    {
        $response = $this->post('/login', [
            'plate_number' => 'b123abc',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'plate_number' => 'B123ABC',
            'name' => null,
        ]);
    }

    public function test_plate_number_spaces_and_dashes_are_stripped_on_login(): void
    {
        $response = $this->post('/login', [
            'plate_number' => 'b 12-3 abc',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'plate_number' => 'B123ABC',
        ]);
    }

    public function test_plate_number_diacritics_are_normalized_on_login(): void
    {
        $response = $this->post('/login', [
            'plate_number' => 'B 123 ĂÎȚ',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'plate_number' => 'B123AIT',
        ]);
    }

    public function test_user_can_log_in_with_formatted_plate_matching_stored_value(): void
    {
        User::factory()->create([
            'plate_number' => 'B123ABC',
        ]);

        $response = $this->post('/login', [
            'plate_number' => 'B 123-ABC',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs(User::query()->where('plate_number', 'B123ABC')->first());
    }

    public function test_lowercase_input_logs_in_existing_uppercase_user(): void
    {
        User::factory()->create([
            'plate_number' => 'B123ABC',
        ]);

        $response = $this->post('/login', [
            'plate_number' => 'b123abc',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs(User::query()->where('plate_number', 'B123ABC')->first());
        $this->assertDatabaseCount('users', 1);
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_home(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_is_redirected_from_login_to_dashboard(): void
    {
        $user = User::factory()->create([
            'plate_number' => 'B123ABC',
        ]);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/dashboard');
    }
}
