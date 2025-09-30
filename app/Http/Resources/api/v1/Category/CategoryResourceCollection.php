<?php
namespace App\Http\Resources\api\v1\Category;

use Illuminate\Http\Resources\Json\ResourceCollection;
class CategoryResourceCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
        ];
    }
}

