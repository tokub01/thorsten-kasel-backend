<?php

namespace App\Http\Requests\api\v1\Exhibition;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExhibitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "title" => "nullable|string",
            "description" => "nullable|string",
            "text" => "nullable|string",
            "date" => "nullable|date",
            "image" => "nullable|string",
            "isActive" => "nullable",
        ];
    }
}
