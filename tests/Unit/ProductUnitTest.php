<?php

namespace Tests\Unit\api\v1\Product;

use App\Http\Controllers\api\v1\Product\ProductController;
use App\Http\Requests\api\v1\Product\ProductRequest;
use App\Http\Responses\api\v1\Product\ProductResource;
use App\Http\Responses\api\v1\Product\ProductResourceCollection;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\TestCase;
use Throwable;

class ProductUnitTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testIndexReturnsJsonResponseWithProductCollection()
    {
        $mockProducts = Mockery::mock('Illuminate\Database\Eloquent\Collection');

        $controller = Mockery::mock(ProductController::class)->makePartial();

        // Mock Product::all()
        Product::shouldReceive('all')->once()->andReturn($mockProducts);

        $response = $controller->index();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertInstanceOf(ProductResourceCollection::class, $response->getData()->resource);
    }

    public function testShowReturnsJsonResponseWithProductResource()
    {
        $product = Mockery::mock(Product::class);

        $controller = new ProductController();

        $response = $controller->show($product);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertInstanceOf(ProductResource::class, $response->getData()->resource);
    }

    public function testStoreCreatesProductAndReturnsJsonResponse()
    {
        $requestData = ['name' => 'Test Product', 'price' => 10.0];

        $requestMock = Mockery::mock(ProductRequest::class);
        $requestMock->shouldReceive('validated')->once()->andReturn($requestData);

        // Mock Product::create
        Product::shouldReceive('create')->once()->with($requestData)->andReturn(new Product($requestData));

        // Mock Product::all for returning the collection
        Product::shouldReceive('all')->once()->andReturn(collect());

        $controller = new ProductController();

        $response = $controller->store($requestMock);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertInstanceOf(ProductResourceCollection::class, $response->getData()->resource);
    }

    public function testUpdateUpdatesProductAndReturnsJsonResponse()
    {
        $product = Mockery::mock(Product::class);
        $product->shouldReceive('update')->once()->with(['name' => 'Updated Name']);

        $requestMock = Mockery::mock(ProductRequest::class);
        $requestMock->shouldReceive('validated')->once()->andReturn(['name' => 'Updated Name']);

        // Mock Product::all
        Product::shouldReceive('all')->once()->andReturn(collect());

        $controller = new ProductController();

        $response = $controller->update($requestMock, $product);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertInstanceOf(ProductResourceCollection::class, $response->getData()->resource);
    }

    public function testDestroyDeletesProductAndReturnsJsonResponse()
    {
        $product = Mockery::mock(Product::class);
        $product->shouldReceive('delete')->once();

        $controller = new ProductController();

        $response = $controller->destroy($product);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Product deleted successfully.', $response->getData()->message);
    }

    public function testIndexHandlesException()
    {
        Product::shouldReceive('all')->once()->andThrow(new \Exception('DB error'));

        $controller = new ProductController();

        $response = $controller->index();

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString('Unable to retrieve products.', $response->getData()->message);
    }

    // Ähnliche Exception-Tests für show, store, update, destroy kannst du analog ergänzen.
}
