<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test fetching all products (index) without authentication.
     */
    public function testIndexReturnsAllProducts()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price', 'image_url']
                ]
            ]);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test show product detail without authentication.
     */
    public function testShowReturnsProduct()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $product->id]);
    }

    /**
     * Test store product requires authentication.
     */
    public function testStoreRequiresAuthentication()
    {
        $response = $this->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'price' => 99.99,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test store product with valid data and image upload.
     */
    public function testAuthenticatedUserCanStoreProductWithImage()
    {
        Sanctum::actingAs(User::factory()->create());

        Storage::fake('s3');

        $file = UploadedFile::fake()->image('product.jpg');

        $payload = [
            'name' => 'Test Product',
            'price' => 199.99,
            'image' => $file,
        ];

        $response = $this->postJson('/api/v1/products', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Product']);

        Storage::disk('s3')->assertExists('products/' . $file->hashName());

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 199.99,
        ]);
    }

    /**
     * Test update product with authentication.
     */
    public function testAuthenticatedUserCanUpdateProduct()
    {
        Sanctum::actingAs(User::factory()->create());

        $product = Product::factory()->create([
            'name' => 'Old Name',
            'price' => 10,
        ]);

        $payload = [
            'name' => 'Updated Name',
            'price' => 20,
        ];

        $response = $this->putJson("/api/v1/products/{$product->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 20,
        ]);
    }

    /**
     * Test delete product with authentication.
     */
    public function testAuthenticatedUserCanDeleteProduct()
    {
        Sanctum::actingAs(User::factory()->create());

        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Product deleted successfully.']);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * Test validation errors on store.
     */
    public function testStoreValidationErrors()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/v1/products', [
            'name' => '',
            'price' => 'abc'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price']);
    }
}
