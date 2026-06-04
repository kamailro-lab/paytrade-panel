<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        $isUpdate = $userId !== null;

        return [
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                Password::min(8)->letters()->numbers(),
                'confirmed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Podaj imię.',
            'email.required' => 'Podaj email.',
            'email.email' => 'Nieprawidłowy email.',
            'email.unique' => 'Email już istnieje w bazie.',
            'password.required' => 'Podaj hasło.',
            'password.confirmed' => 'Hasła nie są identyczne.',
            'password.min' => 'Hasło min. 8 znaków.',
        ];
    }
}
