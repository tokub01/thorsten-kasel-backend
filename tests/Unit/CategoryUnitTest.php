<?php

namespace Tests\Unit;

use App\Http\Controllers\api\v1\Category\CategoryController;
use App\Http\Requests\api\v1\Category\CategoryRequest;
use App\Http\Resources\api\v1\Category\CategoryResource;
use App\Http\Resources\api\v1\Category\CategoryResourceCollection;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Mockery;
use PHPUnit\Framework\TestCase;

class CategoryUnitTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testIndexReturnsJsonResponseWithCategoryCollection()
    {
        $mockCategories = Mockery::mock('Illuminate\Database\Eloquent\Collection');

        Category::shouldReceive('all')->once()->andReturn($mockCategories);

        $controller = new CategoryController();

        $response = $controller->index(new CategoryRequest());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertInstanceOf(CategoryResponseCollection::class, $response->getData()->resource);
    }

    public function testShowReturnsJsonResponseWithCategoryResource()
    {
        $category = Mockery::mock(Category::class);

        $controller = new CategoryController();

        $response = $controller->show(new CategoryRequest(), $category);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertInstanceOf(CategoryResponse::class, $response->getData()->resource);
    }

    public function testStoreCreatesCategoryAndReturnsJsonResponse()
    {
        $requestData = ['name' => 'Test Category', 'description' => 'desc'];

        $requestMock = Mockery::mock(CategoryRequest::class);
        $requestMock->shouldReceive('validated')->once()->andReturn($requestData);

        Category::shouldReceive('create')->once()->with($requestData)->andReturn(new Category($requestData));

        Category::shouldReceive('all')->once()->andReturn(collect());

        $controller = new CategoryController();

        $response = $controller->store($requestMock);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertInstanceOf(CategoryResponseCollection::class, $response->getData()->resource);
    }

    public function testUpdateUpdatesCategoryAndReturnsJsonResponse()
    {
        $category = Mockery::mock(Category::class);
        $category->shouldReceive('update')->once()->with(['name' => 'Updated Category']);

        $requestMock = Mockery::mock(CategoryRequest::class);
        $requestMock->shouldReceive('validated')->once()->andReturn(['name' => 'Updated Category']);

        Category::shouldReceive('all')->once()->andReturn(collect());

        $controller = new CategoryController();

        $response = $controller->update($requestMock, $category);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertInstanceOf(CategoryResponseCollection::class, $response->getData()->resource);
    }

    public function testDestroyDeletesCategoryAndReturnsJsonResponse()
    {
        $category = Mockery::mock(Category::class);
        $category->shouldReceive('delete')->once();

        $controller = new CategoryController();

        $response = $controller->destroy(new CategoryRequest());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Category deleted successfully.', $response->getData()->message);
    }
}
