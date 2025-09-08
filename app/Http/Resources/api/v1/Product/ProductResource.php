<?php

namespace App\Http\Responses\api\v1\Product;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $temporaryImageUrl = null;
        if ($this->image) {
            $temporaryImageUrl = Storage::disk('s3')->temporaryUrl(
                $this->image,
                now()->addMinutes(5)
            );
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'image' => $temporaryImageUrl,
        ];
    }
}
