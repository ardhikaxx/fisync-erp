<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AR\Customer;
use App\Models\AP\Supplier;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        Customer::firstOrCreate(['email' => 'pt.abadi@example.com'], [
            'name' => 'PT. Abadi Jaya',
            'phone' => '081234567890',
            'address' => 'Jl. Sudirman No. 1, Jakarta'
        ]);

        Customer::firstOrCreate(['email' => 'cv.makmur@example.com'], [
            'name' => 'CV. Makmur Sentosa',
            'phone' => '081298765432',
            'address' => 'Jl. Thamrin No. 2, Jakarta'
        ]);

        Supplier::firstOrCreate(['email' => 'pt.suplier@example.com'], [
            'name' => 'PT. Supplier Utama',
            'phone' => '081122334455',
            'address' => 'Kawasan Industri Pulo Gadung'
        ]);
    }
}
