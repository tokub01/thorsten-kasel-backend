<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsAllCategories()
    {
        Category::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description']
                ]
            ]);
        $this->assertCount(5, $response->json('data'));
    }

    public function testShowReturnsCategory()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $category->id]);
    }

    public function testStoreRequiresAuthentication()
    {
        $response = $this->postJson('/api/v1/categories', [
            'name' => 'New Category',
        ]);

        $response->assertStatus(401);
    }

    public function testAuthenticatedUserCanStoreCategory()
    {
        Sanctum::actingAs(User::factory()->create());

        $payload = [
            'name' => 'New Category',
            'description' => 'Category description',
        ];

        $response = $this->postJson('/api/v1/categories', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'New Category']);

        $this->assertDatabaseHas('categories', [
            'name' => 'New Category',
        ]);
    }

    public function testUpdateCategory()
    {
        Sanctum::actingAs(User::factory()->create());

        $category = Category::factory()->create([
            'name' => 'Old Category',
        ]);

        $payload = [
            'name' => 'Updated Category',
            'description' => 'Updated description',
        ];

        $response = $this->putJson("/api/v1/categories/{$category->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Category']);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    public function testDeleteCategory()
    {
        Sanctum::actingAs(User::factory()->create());

        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Category deleted successfully.']);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function testStoreValidationErrors()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/v1/categories', [
            'name' => '', // required
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
