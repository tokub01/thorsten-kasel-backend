<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_in_a_user_successfully(): void
    {
        $password = 'geheim123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'user' => ['id', 'name', 'email', 'role'],
                'token',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Anmeldung erfolgreich.',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_it_fails_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Die eingegebenen Zugangsdaten sind ungültig.',
            ]);
    }

    public function test_it_registers_a_new_user_successfully(): void
    {
        $payload = [
            'name' => 'Neuer User',
            'email' => 'neueruser@example.com',
            'password' => 'geheim123',
            'password_confirmation' => 'geheim123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'user' => ['id', 'name', 'email'],
                'token',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Registration successful.',  // Falls der Text auch deutsch sein soll, hier anpassen
                'user' => [
                    'name' => 'Neuer User',
                    'email' => 'neueruser@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'neueruser@example.com',
            'name' => 'Neuer User',
        ]);
    }

    public function test_it_fails_registration_with_existing_email(): void
    {
        $existingUser = User::factory()->create();

        $payload = [
            'name' => 'Test User',
            'email' => $existingUser->email,
            'password' => 'geheim123',
            'password_confirmation' => 'geheim123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_logs_out_authenticated_user(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Erfolgreich ausgeloggt.',
            ]);
    }

    public function test_it_fails_logout_if_not_authenticated(): void
    {
        $response = $this->postJson('/api/auth/logout');

        // Laravel gibt hier standardmäßig nur "message": "Unauthenticated." zurück
        // Falls du eine eigene Response möchtest, musst du den Handler anpassen.
        $response->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
