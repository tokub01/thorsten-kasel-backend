<?php

namespace App\Http\Resources\api\v1\Category;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
{
    public function toArray($request) : array
    {
        $temporaryImageUrl = null;
        if (Product::where('id',$this->product_id)->exists()) {
            $temporaryImageUrl = Storage::disk('s3')->temporaryUrl(
                Product::find($this->product_id)->image,
                now()->addMinutes(5)
            );
        }

        return [
            'id' => $this->id,
            'image' => $temporaryImageUrl,
            'name' => $this->name,
        ];
    }
}
