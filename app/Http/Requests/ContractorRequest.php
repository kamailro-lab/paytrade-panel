<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContractorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:supplier,customer,both'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:200'],
            'eir_code' => ['nullable', 'string', 'max:8', 'regex:/^[A-Z0-9 ]{3,8}$/i'],
            'vat_number' => ['nullable', 'string', 'max:30'],
            'ppsn' => ['nullable', 'string', 'max:12'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Wybierz typ (dostawca, klient lub oboje).',
            'name.required' => 'Podaj imię/nazwisko lub nazwę firmy.',
            'email.email' => 'Nieprawidłowy email.',
            'eir_code.regex' => 'Eircode wygląda jak np. R32RC43 lub E34VK30.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('eir_code')) {
            $this->merge(['eir_code' => strtoupper(preg_replace('/\s+/', '', $this->input('eir_code')))]);
        }
    }
}
