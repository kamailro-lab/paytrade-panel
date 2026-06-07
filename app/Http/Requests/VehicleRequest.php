<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'registration' => [
                'required',
                'string',
                'min:4',
                'max:12',
                'regex:/^[A-Z0-9\-]+$/',
                Rule::unique('vehicles', 'registration')->ignore($vehicleId),
            ],
            'logbook_no' => ['nullable', 'string', 'max:20'],
            'make' => ['required', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:80'],
            'year' => ['nullable', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
            'engine_cc' => ['nullable', 'integer', 'min:50', 'max:10000'],
            'fuel' => ['nullable', 'in:petrol,diesel,hybrid,electric,lpg'],
            'color' => ['nullable', 'string', 'max:40'],
            'mileage_km' => ['nullable', 'integer', 'min:0', 'max:2000000'],
            'body' => ['nullable', 'in:sedan,hatchback,suv,coupe,estate,mpv,convertible,pickup'],
            'doors' => ['nullable', 'integer', 'min:2', 'max:7'],
            'status' => ['required', 'in:stock,sold,service,written_off'],
            'nct_expiry' => ['nullable', 'date'],
            'nct_passed' => ['nullable', 'boolean'],
            'service_done' => ['nullable', 'boolean'],
            'service_date' => ['nullable', 'date'],
            'timing_belt_checked' => ['nullable', 'boolean'],
            'timing_belt_date' => ['nullable', 'date'],
            'target_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'supplier_contractor_id' => ['nullable', 'integer', 'exists:contractors,id'],
            'source' => ['nullable', 'in:uk_auction,uk_dealer,ie_private,ie_dealer,ie_auction,eu_import,other'],
            'source_detail' => ['nullable', 'string', 'max:200'],
            'paid_cash' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'paid_bank' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function passedValidation(): void
    {
        // Checkboxy: jeśli nie zaznaczone → false (HTML form nie wysyła unchecked)
        foreach (['nct_passed', 'service_done', 'timing_belt_checked'] as $f) {
            if (!$this->has($f)) $this->merge([$f => false]);
        }
    }

    public function messages(): array
    {
        return [
            'registration.required' => 'Podaj numer rejestracyjny.',
            'registration.regex' => 'Tylko litery, cyfry i myślniki. Przykład: 131-D-1108 lub 131D1108.',
            'registration.unique' => 'Auto z tą rejestracją już istnieje.',
            'registration.min' => 'Rejestracja za krótka.',
            'make.required' => 'Podaj markę auta.',
            'model.required' => 'Podaj model auta.',
            'year.min' => 'Rok nie może być wcześniejszy niż 1950.',
            'year.max' => 'Rok nie może być z przyszłości.',
            'engine_cc.min' => 'Pojemność silnika musi być >= 50ccm.',
            'mileage_km.min' => 'Przebieg nie może być ujemny.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('registration')) {
            $raw = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $this->input('registration')));

            if (preg_match('/^(\d{2,3})([A-Z]{1,2})(\d{1,6})$/', $raw, $m)) {
                $this->merge(['registration' => "{$m[1]}-{$m[2]}-{$m[3]}"]);
            } else {
                $this->merge(['registration' => $raw]);
            }
        }
    }
}
