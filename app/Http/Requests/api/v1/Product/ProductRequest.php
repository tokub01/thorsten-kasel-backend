<?php

namespace App\Http\Requests\api\v1\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // ✅ nullable!
            'price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'isActive' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Der Titel ist erforderlich.',
            'title.max' => 'Der Titel darf maximal 255 Zeichen lang sein.',
            'description.max' => 'Die Beschreibung darf maximal 2000 Zeichen lang sein.',
            'image.image' => 'Die Datei muss ein Bild sein.',
            'image.mimes' => 'Das Bild muss vom Typ jpeg, jpg, png, gif oder webp sein.',
            'image.max' => 'Das Bild darf maximal 5MB groß sein.',
            'price.numeric' => 'Der Preis muss eine Zahl sein.',
            'price.min' => 'Der Preis muss mindestens 0 sein.',
            'category_id.exists' => 'Die ausgewählte Kategorie existiert nicht.',
            'isActive.required' => 'Der Status (aktiv/inaktiv) ist erforderlich.',
            'isActive.boolean' => 'Der Status muss ein Boolean-Wert sein.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // isActive von String zu Boolean konvertieren (für FormData)
        if ($this->has('isActive')) {
            $this->merge([
                'isActive' => filter_var($this->isActive, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }

        // Sicherstellen, dass category_id null ist wenn leer
        if ($this->has('category_id') && $this->category_id === '') {
            $this->merge([
                'category_id' => null,
            ]);
        }

        // Price als float konvertieren wenn vorhanden
        if ($this->has('price') && $this->price !== null && $this->price !== '') {
            $this->merge([
                'price' => (float) $this->price,
            ]);
        }
    }
}