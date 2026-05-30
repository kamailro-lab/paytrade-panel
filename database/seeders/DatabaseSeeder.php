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
            'company_name' => 'Twoja firma sp. z o.o.',
            'company_address' => 'Dublin, Ireland',
            'company_vat_number' => 'IE0000000A',
            'company_phone' => '+353 1 000 0000',
            'invoice_prefix' => date('Y'),
            'invoice_next_number' => 1,
        ];
        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => (string) $value]);
        }
    }
}
