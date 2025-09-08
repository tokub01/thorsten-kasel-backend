<?php
namespace App\Http\Responses\api\v1\Product;

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
