<?php
namespace App\Http\Resources\api\v1\User;

use Illuminate\Http\Resources\Json\ResourceCollection;
class UserResourceCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
        ];
    }
}
