<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_returns_a_list_of_categories()
    {
        Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name']]])
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_returns_a_single_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name']])
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.name', $category->name);
    }

    #[Test]
    public function it_creates_a_new_category_when_authenticated()
    {
        $payload = ['name' => 'Neue Kategorie'];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/categories', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Neue Kategorie');

        $this->assertDatabaseHas('categories', ['name' => 'Neue Kategorie']);
    }

    #[Test]
    public function it_fails_to_create_category_when_not_authenticated()
    {
        $payload = ['name' => 'Nicht erlaubt'];

        $response = $this->postJson('/api/categories', $payload);

        $response->assertUnauthorized();
    }

    #[Test]
    public function it_validates_category_creation()
    {
        $payload = ['name' => '']; // leer = invalid

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/categories', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    #[Test]
    public function it_updates_a_category_when_authenticated()
    {
        $category = Category::factory()->create(['name' => 'Alt']);

        $payload = ['name' => 'Neu'];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/categories/{$category->id}", $payload);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Neu');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Neu']);
    }

    #[Test]
    public function it_fails_to_update_or_delete_when_not_authenticated()
    {
        $category = Category::factory()->create();

        $updateResponse = $this->putJson("/api/categories/{$category->id}", ['name' => 'Test']);
        $deleteResponse = $this->deleteJson("/api/categories/{$category->id}");

        $updateResponse->assertUnauthorized();
        $deleteResponse->assertUnauthorized();
    }

    #[Test]
    public function it_soft_deletes_a_category_when_authenticated()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/categories/{$category->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Kategorie erfolgreich gelöscht.']);

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }
}
