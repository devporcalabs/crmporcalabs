<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin User
        User::updateOrCreate(
            ['email' => 'admin@porcalabs.com'],
            [
                'name' => 'Admin Porcalabs',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Seed Default Settings
        $defaultSettings = [
            'company_name' => 'PT Porcalabs Digital Indonesia',
            'company_address' => 'Grand Galaxy City, Ruko Rose Garden Blok RRG 5 No. 22, Jaka Setia, Bekasi Selatan, Kota Bekasi, Jawa Barat 17147',
            'company_phone' => '6281282229411', // WhatsApp template target
            'company_email' => 'finance@porcalabs.com',
            'company_npwp' => '42.123.456.7-002.000',
            'company_logo' => '', // Empty for placeholder or upload
            'bank_accounts' => json_encode([
                [
                    'bank' => 'Bank Mandiri',
                    'number' => '167-00-0123456-7',
                    'holder' => 'PT Porcalabs Digital Indonesia'
                ],
                [
                    'bank' => 'BCA',
                    'number' => '577-098-7654',
                    'holder' => 'PT Porcalabs Digital Indonesia'
                ]
            ]),
            'invoice_prefix' => 'INV',
            'default_ppn' => '11.00',
            'wa_confirmation_number' => '6281282229411', // For confirmation instruction text
            'digital_signature_name' => 'Muhammad Fachry',
            'digital_signature_title' => 'Direktur Utama',
        ];

        foreach ($defaultSettings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
