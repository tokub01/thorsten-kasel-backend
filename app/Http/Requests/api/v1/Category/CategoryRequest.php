<?php

namespace App\Http\Requests\api\v1\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
        $categoryId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            'product_id' => 'nullable|exists:products,id',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Bitte gib einen Namen für die Kategorie an.',
            'name.string' => 'Der Kategoriename muss ein Text sein.',
            'name.max' => 'Der Kategoriename darf maximal 255 Zeichen lang sein.',
            'name.unique' => 'Diese Kategorie existiert bereits.',
            'product_id.exists' => 'Das ausgewählte Produkt existiert nicht.',
        ];
    }
}
