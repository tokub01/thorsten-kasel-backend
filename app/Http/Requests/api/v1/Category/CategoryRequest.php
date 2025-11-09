<?php

namespace App\Http\Requests\api\v1\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

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
        // Hole die Category ID mit ALLEN möglichen Methoden
        $categoryId = $this->getCategoryId();

        // Unterscheide zwischen POST (create) und PUT (update)
        $isUpdate = $this->isMethod('PUT') || $this->input('_method') === 'PUT';

        // KRITISCHES Logging
        Log::info('🔍 CategoryRequest Validation', [
            'method' => $this->method(),
            '_method' => $this->input('_method'),
            'isUpdate' => $isUpdate,
            'categoryId' => $categoryId,
            'name' => $this->input('name'),
            'will_ignore_unique_for_id' => ($isUpdate && $categoryId) ? "YES ✅ (ID: {$categoryId})" : 'NO ❌',
        ]);

        // WARNUNG wenn Update ohne ID
        if ($isUpdate && !$categoryId) {
            Log::error('❌ CRITICAL: Update-Request ohne Category-ID erkannt!', [
                'route_params' => $this->route()->parameters(),
                'url' => $this->url(),
                'segments' => $this->segments(),
            ]);
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Bei Update UND wenn wir eine ID haben: Ignoriere die aktuelle Kategorie
                $isUpdate && $categoryId
                    ? Rule::unique('categories', 'name')->ignore($categoryId)
                    : Rule::unique('categories', 'name'),
            ],
            'product_id' => 'nullable|exists:products,id',
        ];
    }

    /**
     * Hole die Category-ID mit mehreren Fallback-Methoden
     */
    protected function getCategoryId()
    {
        // Methode 1: Route Parameter 'category'
        $category = $this->route('category');
        if ($category) {
            $id = is_object($category) ? $category->id : $category;
            Log::info('Found category ID via route parameter', ['id' => $id, 'type' => is_object($category) ? 'model' : 'value']);
            return $id;
        }

        // Methode 2: Route Parameter 'id'
        $id = $this->route('id');
        if ($id) {
            $id = is_object($id) ? $id->id : $id;
            Log::info('Found category ID via id parameter', ['id' => $id]);
            return $id;
        }

        // Methode 3: Aus URL Segments extrahieren
        $segments = $this->segments();
        $categoryIndex = array_search('categories', $segments);
        if ($categoryIndex !== false && isset($segments[$categoryIndex + 1])) {
            $potentialId = $segments[$categoryIndex + 1];
            if (is_numeric($potentialId)) {
                Log::info('Found category ID via URL segments', ['id' => $potentialId]);
                return (int)$potentialId;
            }
        }

        // Methode 4: Alle Route-Parameter durchsuchen
        $params = $this->route()->parameters();
        Log::info('All route parameters', ['params' => $params]);

        foreach ($params as $key => $value) {
            if (is_numeric($value)) {
                Log::info('Found numeric parameter in route', ['key' => $key, 'value' => $value]);
                return (int)$value;
            }
            if (is_object($value) && method_exists($value, 'getKey')) {
                Log::info('Found model in route parameters', ['key' => $key, 'id' => $value->getKey()]);
                return $value->getKey();
            }
        }

        Log::warning('Could not find category ID with any method!');
        return null;
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
            'name.unique' => 'Eine Kategorie mit dem Namen ":input" existiert bereits. Bitte wähle einen anderen Namen.',
            'product_id.exists' => 'Das ausgewählte Produkt existiert nicht.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Kategoriename',
            'product_id' => 'Produkt',
        ];
    }
}

