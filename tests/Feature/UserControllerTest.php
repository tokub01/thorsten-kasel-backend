<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    #[Test]
    public function it_lists_all_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/users');

        $response->assertOk();

        foreach ($response->json('data') as $user) {
            $this->assertArrayHasKey('id', $user);
            $this->assertArrayHasKey('name', $user);
            $this->assertArrayHasKey('email', $user);
        }
    }

    #[Test]
    public function it_shows_a_single_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')->getJson("/api/users/{$user->id}");

        $response->assertOk();

        $data = $response->json('data');

        $this->assertEquals($user->id, $data['id']);
        $this->assertEquals($user->name, $data['name']);
        $this->assertEquals($user->email, $data['email']);
    }

    #[Test]
    public function it_creates_a_user(): void
    {
        $payload = [
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
            'password' => 'geheim123',
            'password_confirmation' => 'geheim123',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/users', $payload);

        $response->assertCreated();

        $data = $response->json('data');
        $this->assertEquals('Max Mustermann', $data['name']);

        $this->assertDatabaseHas('users', [
            'email' => 'max@example.com',
            'name' => 'Max Mustermann',
        ]);
    }

    #[Test]
    public function it_requires_authentication_to_create_user(): void
    {
        $payload = [
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
            'password' => 'geheim123',
            'password_confirmation' => 'geheim123',
        ];

        $this->postJson('/api/users', $payload)->assertUnauthorized();
    }

    #[Test]
    public function it_validates_required_fields_when_creating_user(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'password']);
    }

    #[Test]
    public function it_updates_a_user(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name' => 'Neuer Name',
            'email' => 'new@example.com',
            'password' => 'geheim123',
            'password_confirmation' => 'geheim123',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/users/{$user->id}", $payload);

        $response->assertOk();

        $data = $response->json('data');
        $this->assertEquals('Neuer Name', $data['name']);

        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com',
            'name' => 'Neuer Name',
        ]);
    }

    #[Test]
    public function it_fails_to_update_or_delete_when_not_authenticated(): void
    {
        $user = User::factory()->create();

        $this->putJson("/api/users/{$user->id}", [
            'name' => 'Unauthenticated',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ])->assertUnauthorized();

        $this->deleteJson("/api/users/{$user->id}")
            ->assertUnauthorized();
    }

    #[Test]
    public function it_deletes_a_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')->deleteJson("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJson(['message' => 'User deleted successfully.']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
