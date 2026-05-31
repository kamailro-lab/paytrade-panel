<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const KEYS = [
        'company_name' => 'Nazwa firmy',
        'company_address' => 'Adres',
        'company_eir_code' => 'Eircode',
        'company_vat_number' => 'VAT Number',
        'company_phone' => 'Telefon',
        'company_email' => 'Email',
        'company_iban' => 'IBAN',
        'company_bank' => 'Bank',
    ];

    public function edit(): View
    {
        $values = collect(self::KEYS)->mapWithKeys(fn ($label, $key) => [$key => Setting::get($key, '')]);

        return view('settings.edit', [
            'keys' => self::KEYS,
            'values' => $values,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = collect(self::KEYS)->mapWithKeys(fn ($_, $key) => [$key => ['nullable', 'string', 'max:200']])->all();
        $data = $request->validate($rules);

        foreach ($data as $key => $value) {
            Setting::put($key, $value ?? '');
        }

        return redirect()->route('settings.edit')->with('success', 'Dane firmy zapisane.');
    }
}
