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
            'title' => 'required|string',
            'description' => 'required|string',
            'image' => 'required|image',
            'price' => '', // Optionales Feld, du kannst hier ggf. zusätzliche Regeln ergänzen
            'category_id' => 'required|exists:categories,id',
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
            'title.required' => 'Bitte gib einen Titel für das Produkt an.',
            'title.string' => 'Der Titel muss ein Text sein.',

            'description.required' => 'Bitte gib eine Beschreibung für das Produkt an.',
            'description.string' => 'Die Beschreibung muss ein Text sein.',

            'image.required' => 'Bitte lade ein Produktbild hoch.',
            'image.image' => 'Die Datei muss ein Bild sein (jpeg, png, bmp, gif, svg, webp).',

            'category_id.required' => 'Bitte wähle eine Kategorie aus.',
            'category_id.exists' => 'Die gewählte Kategorie ist ungültig.',
        ];
    }
}
