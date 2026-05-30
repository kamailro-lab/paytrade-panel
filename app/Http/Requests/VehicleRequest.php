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
                'max:12',
                'regex:/^\d{2,3}-[A-Z]{1,2}-\d{1,6}$/',
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
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'registration.required' => 'Podaj numer rejestracyjny.',
            'registration.regex' => 'Nieprawidłowy format. Przykład: 152-D-12345.',
            'registration.unique' => 'Auto z tą rejestracją już istnieje.',
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
            $this->merge([
                'registration' => strtoupper(trim($this->input('registration'))),
            ]);
        }
    }
}
