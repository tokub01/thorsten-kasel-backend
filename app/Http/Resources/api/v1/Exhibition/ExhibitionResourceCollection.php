<?php
namespace App\Http\Resources\api\v1\Exhibition;

use Illuminate\Http\Resources\Json\ResourceCollection;
class NewsResourceCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
        ];
    }
}
