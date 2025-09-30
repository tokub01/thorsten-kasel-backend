<?php
namespace App\Http\Resources\api\v1\Product;

use Illuminate\Http\Resources\Json\ResourceCollection;
class ProductResourceCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
        ];
    }
}
