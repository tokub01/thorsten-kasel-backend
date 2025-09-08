<?php

namespace App\Http\Requests\api\v1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    /**
     * Sets messages for validation options
     *
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'email.required'    => 'Bitte geben Sie Ihre E-Mail Adresse ein.',
            'password.required' => 'Bitte geben Sie Ihr Passwort ein.',
        ];
    }
}
