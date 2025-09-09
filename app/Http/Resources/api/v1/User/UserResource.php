<?php

namespace App\Http\Resources\api\v1\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'email' => $this->email,
            'name' => $this->name,
            'biography' => $this->biography,
        ];
    }
}
