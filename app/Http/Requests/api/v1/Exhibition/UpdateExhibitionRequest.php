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
        return true; // Oder deine Auth-Logik
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
            'description' => 'nullable|string|max:1000',
            'text' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // ✅ nullable!
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
            'description.max' => 'Die Beschreibung darf maximal 1000 Zeichen lang sein.',
            'image.image' => 'Die Datei muss ein Bild sein.',
            'image.mimes' => 'Das Bild muss vom Typ jpeg, jpg, png, gif oder webp sein.',
            'image.max' => 'Das Bild darf maximal 5MB groß sein.',
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
    }
}
