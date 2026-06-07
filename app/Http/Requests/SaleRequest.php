<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'sale_price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'sale_date' => ['nullable', 'date'],
            'contractor_id' => ['nullable', 'integer', 'exists:contractors,id'],
            'warranty_months' => ['nullable', 'integer', 'min:0', 'max:60'],
            'deposit' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'paid_cash' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'paid_bank' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'sale_price.required' => 'Podaj cenę sprzedaży.',
            'sale_price.numeric' => 'Cena sprzedaży musi być liczbą.',
            'sale_price.min' => 'Cena sprzedaży nie może być ujemna.',
            'contractor_id.exists' => 'Wybrany klient nie istnieje.',
            'warranty_months.max' => 'Maksymalna gwarancja to 60 miesięcy.',
        ];
    }
}
