<?php

namespace App\Http\Requests\api\v1\User;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'biography' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Bitte gib deinen Namen an.',
            'name.string' => 'Der Name muss ein Text sein.',
            'name.max' => 'Der Name darf maximal 255 Zeichen lang sein.',

            'email.required' => 'Bitte gib deine E-Mail-Adresse an.',
            'email.email' => 'Bitte gib eine gültige E-Mail-Adresse an.',
            'email.unique' => 'Diese E-Mail-Adresse ist bereits registriert.',

            'password.required' => 'Bitte gib ein Passwort an.',
            'password.string' => 'Das Passwort muss ein Text sein.',
            'password.min' => 'Das Passwort muss mindestens 8 Zeichen lang sein.',
            'password.confirmed' => 'Die Passwortbestätigung stimmt nicht überein.',

            'biography.string' => 'Die Biografie muss ein Text sein.',
        ];
    }
}
