<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@cars.ie'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'info@paytrade.ie'],
            [
                'name' => 'Paytrade',
                'password' => Hash::make('paytrade123'),
                'email_verified_at' => now(),
            ]
        );

        $defaults = [
            'company_name' => 'Paytrade / MRtardex',
            'company_address' => 'Dublin, Ireland',
            'company_eir_code' => '',
            'company_vat_number' => '',
            'company_phone' => '',
            'company_email' => 'info@paytrade.ie',
            'company_iban' => '',
            'company_bank' => '',
        ];
        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => (string) $value]);
        }
    }
}
