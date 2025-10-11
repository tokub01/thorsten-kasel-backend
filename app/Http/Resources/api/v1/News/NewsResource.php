<?php

namespace App\Http\Resources\api\v1\News;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class NewsResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'text' => $this->text,
            'image' => $temporaryImageUrl,
            'isActive' => $this->isActive,
        ];
    }
}
