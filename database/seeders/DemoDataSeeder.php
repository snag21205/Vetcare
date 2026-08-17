<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\pet;


class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $khachHang1 = Customer::firstOrCreate(['phone' => '0901234567'], [
            'name'    => 'Nguyễn Thị Mai',
            'email'   => 'mai.nguyen@gmail.com',
            'address' => '123 Đường Lê Lợi, Quận 1, TP.HCM',
        ]);

        Pet::firstOrCreate(['customer_id' => $khachHang1->id, 'name' => 'Mimi'], [
            'species' => 'mèo',
            'breed' => 'Mèo Ba Tư',
            'date_of_birth' => '2022-03-15',
            'gender' => 'female',
            'weight' => 3.5,
        ]);

        Pet::firstOrCreate(['customer_id' => $khachHang1->id, 'name' => 'Bông'], [
            'species' => 'chó',
            'breed' => 'Poodle',
            'gender' => 'male',
            'weight' => 5.2,
        ]);

        $khachHang2 = Customer::firstOrCreate(['phone' => '0987654321'], [
            'name'    => 'Trần Văn Nam',
            'email'   => 'nam.tran@gmail.com',
            'address' => '456 Nguyễn Huệ, Quận 3, TP.HCM',
        ]);

        Pet::firstOrCreate(['customer_id' => $khachHang2->id, 'name' => 'Cún'], [
            'species' => 'chó',
            'breed' => 'Husky',
            'gender' => 'male',
            'weight' => 18.0,
        ]);

        $this->command->info('Demo data created!');
    }
}
