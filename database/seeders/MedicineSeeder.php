<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Medicine;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicines = [
            [
                'name' => 'Amoxicillin 250mg',
                'unit' => 'viên',
                'stock_quantity' => 500,
                'import_price' => 1500,
                'sell_price' => 3000,
                'expiry_date' => '2027-06-30',
                'low_stock_threshold' => 50
            ],

            [
                'name' => 'Doxycycline 100mg',
                'unit' => 'viên',
                'stock_quantity' => 300,
                'import_price' => 2000,
                'sell_price' => 4000,
                'expiry_date' => '2027-12-31',
                'low_stock_threshold' => 30
            ],

            [
                'name' => 'Ivermectin 1%',
                'unit' => 'ml',
                'stock_quantity' => 100,
                'import_price' => 15000,
                'sell_price' => 30000,
                'expiry_date' => '2026-12-31',
                'low_stock_threshold' => 10
            ],

            [
                'name' => 'Vitamin B Complex',
                'unit' => 'viên',
                'stock_quantity' => 8,
                'import_price' => 500,
                'sell_price' => 1000,
                'expiry_date' => '2026-10-31',
                'low_stock_threshold' => 20
            ],
            // stock=8 < threshold=20 → test tính năng cảnh báo tồn kho thấp
        ];

        foreach ($medicines as $med) {
            Medicine::firstOrCreate(['name' => $med['name']], $med);
        }

        $this->command->info('Medicines seeded!');
    }
}
