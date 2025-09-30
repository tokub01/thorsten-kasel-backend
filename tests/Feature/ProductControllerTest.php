<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Testuser anlegen
        $this->user = User::factory()->create();

        // Fake S3 Storage
        Storage::fake('s3');
    }

    #[Test]
    public function it_lists_all_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonStructure(['data']);

        // data ist ein Array mit Items
        $json = $response->json();
        $this->assertIsArray($json['data']);
        $this->assertCount(3, $json['data']);
    }

    #[Test]
    public function it_shows_a_single_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertOk();

        $json = $response->json();

        // KEIN data wrapper, direkt Produktfelder prüfen
        $this->assertArrayHasKey('id', $json);
        $this->assertEquals($product->id, $json['id']);
        $this->assertEquals($product->title, $json['title']);
        $this->assertEquals($product->category_id, $json['category_id']);
    }

    #[Test]
    public function it_creates_a_product_with_image_upload_to_s3()
    {
        $category = Category::factory()->create();

        $image = UploadedFile::fake()->image('product.jpg');

        $payload = [
            'title' => 'Test Produkt',
            'description' => 'Produktbeschreibung',
            'price' => 19.99,
            'category_id' => $category->id,
            'image' => $image,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/products', $payload);

        $response->assertCreated();

        $json = $response->json();

        // KEIN data wrapper, direkt Felder prüfen
        $this->assertArrayHasKey('id', $json);
        $this->assertEquals('Test Produkt', $json['title']);

        // Prüfe ob das Bild im Fake-S3 gespeichert wurde
        Storage::disk('s3')->assertExists('products/' . $image->hashName());

        // Datenbankcheck
        $this->assertDatabaseHas('products', [
            'title' => 'Test Produkt',
            'image' => 'products/' . $image->hashName(),
        ]);
    }

    #[Test]
    public function it_requires_authentication_to_create_products()
    {
        $category = Category::factory()->create();

        $payload = [
            'title' => 'Unauthorized Produkt',
            'description' => 'Keine Auth',
            'price' => 9.99,
            'category_id' => $category->id,
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertUnauthorized();
    }

    #[Test]
    public function it_validates_required_fields_for_product_creation()
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'category_id', 'image']); // price rausgenommen
    }


    #[Test]
    public function it_updates_a_product_and_replaces_image_on_s3()
    {
        $category = Category::factory()->create();

        // Erst Produkt mit altem Bild erstellen
        $oldImage = UploadedFile::fake()->image('alt.jpg');
        $oldImagePath = $oldImage->store('products', 's3');

        $product = Product::factory()->create([
            'image' => $oldImagePath,
            'category_id' => $category->id,
        ]);

        $newImage = UploadedFile::fake()->image('neu.jpg');

        $payload = [
            'title' => 'Neuer Titel',
            'description' => 'Neue Beschreibung',
            'price' => 29.99,
            'category_id' => $category->id,
            'image' => $newImage,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/products/{$product->id}", $payload);

        $response->assertOk();

        $json = $response->json();

        // KEIN data wrapper
        $this->assertArrayHasKey('id', $json);
        $this->assertEquals('Neuer Titel', $json['title']);

        // Altes Bild gelöscht
        Storage::disk('s3')->assertMissing($oldImagePath);

        // Neues Bild gespeichert
        Storage::disk('s3')->assertExists('products/' . $newImage->hashName());

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Neuer Titel',
            'image' => 'products/' . $newImage->hashName(),
        ]);
    }

    #[Test]
    public function it_fails_to_update_or_delete_product_when_not_authenticated()
    {
        $product = Product::factory()->create();

        $responseUpdate = $this->putJson("/api/products/{$product->id}", [
            'title' => 'Unauthorized Update',
            'description' => 'Fail',
            'price' => 10,
            'category_id' => $product->category_id,
        ]);
        $responseUpdate->assertUnauthorized();

        $responseDelete = $this->deleteJson("/api/products/{$product->id}");
        $responseDelete->assertUnauthorized();
    }

    #[Test]
    public function it_soft_deletes_a_product_and_removes_image_from_s3()
    {
        $image = UploadedFile::fake()->image('toDelete.jpg');
        $imagePath = $image->store('products', 's3');

        $product = Product::factory()->create([
            'image' => $imagePath,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson("/api/products/{$product->id}");

        $response->assertOk()
            ->assertJson(['message' => 'Product deleted successfully.']);

        // Bild ist gelöscht
        Storage::disk('s3')->assertMissing($imagePath);

        // Produkt ist soft deleted
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
