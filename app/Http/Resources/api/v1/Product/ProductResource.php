<?php

namespace App\Http\Resources\api\v1\Product;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;

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
            'category_id' => Category::find($this->category_id),
            'title' => $this->title,
            'description' => $this->description,
            'image' => $temporaryImageUrl,
        ];
    }
}
