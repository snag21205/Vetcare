<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicine;
use App\Models\MedicineBatch;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicines = [
            // --- KHÁNG SINH & KHÁNG VIÊM ---
            [
                'name' => 'Amoxicillin + Clavulanic Acid 250mg',
                'unit' => 'viên',
                'stock_quantity' => 350,
                'import_price' => 3500,
                'sell_price' => 7000,
                'expiry_date' => '2027-08-30',
                'low_stock_threshold' => 50,
                'batches' => [
                    ['batch_number' => 'LO-AMX-2026-01', 'qty' => 150, 'expiry' => '2027-04-15'],
                    ['batch_number' => 'LO-AMX-2026-02', 'qty' => 200, 'expiry' => '2027-08-30'],
                ]
            ],
            [
                'name' => 'Doxycycline 100mg',
                'unit' => 'viên',
                'stock_quantity' => 280,
                'import_price' => 2000,
                'sell_price' => 4500,
                'expiry_date' => '2027-12-31',
                'low_stock_threshold' => 30,
                'batches' => [
                    ['batch_number' => 'LO-DOX-2026-01', 'qty' => 280, 'expiry' => '2027-12-31'],
                ]
            ],
            [
                'name' => 'Cefalexin 250mg',
                'unit' => 'viên',
                'stock_quantity' => 150,
                'import_price' => 2200,
                'sell_price' => 5000,
                'expiry_date' => '2027-05-20',
                'low_stock_threshold' => 25,
            ],
            [
                'name' => 'Metronidazole 250mg',
                'unit' => 'viên',
                'stock_quantity' => 200,
                'import_price' => 1200,
                'sell_price' => 3000,
                'expiry_date' => '2027-10-15',
                'low_stock_threshold' => 30,
            ],
            [
                'name' => 'Baytril (Enrofloxacin) 50mg',
                'unit' => 'viên',
                'stock_quantity' => 80,
                'import_price' => 8500,
                'sell_price' => 15000,
                'expiry_date' => '2027-09-30',
                'low_stock_threshold' => 15,
            ],
            [
                'name' => 'Meloxicam 1.5mg/ml (Giảm đau, kháng viêm)',
                'unit' => 'lọ',
                'stock_quantity' => 45,
                'import_price' => 110000,
                'sell_price' => 180000,
                'expiry_date' => '2027-07-31',
                'low_stock_threshold' => 10,
            ],
            [
                'name' => 'Prednisolone 5mg',
                'unit' => 'viên',
                'stock_quantity' => 120,
                'import_price' => 800,
                'sell_price' => 2000,
                'expiry_date' => '2027-03-31',
                'low_stock_threshold' => 20,
            ],

            // --- HÔ HẤP & TIÊU HÓA ---
            [
                'name' => 'Bromhexin 8mg (Long đờm giảm ho)',
                'unit' => 'viên',
                'stock_quantity' => 180,
                'import_price' => 600,
                'sell_price' => 1500,
                'expiry_date' => '2027-11-30',
                'low_stock_threshold' => 30,
            ],
            [
                'name' => 'Men vi sinh Bio-Rumi tiêu hóa',
                'unit' => 'gói',
                'stock_quantity' => 250,
                'import_price' => 4500,
                'sell_price' => 9000,
                'expiry_date' => '2027-06-30',
                'low_stock_threshold' => 40,
            ],
            [
                'name' => 'Men vi sinh Enterogermina',
                'unit' => 'ống',
                'stock_quantity' => 100,
                'import_price' => 8000,
                'sell_price' => 15000,
                'expiry_date' => '2027-04-30',
                'low_stock_threshold' => 20,
            ],
            [
                'name' => 'Dung dịch Oresol bù nước & điện giải',
                'unit' => 'gói',
                'stock_quantity' => 300,
                'import_price' => 2500,
                'sell_price' => 5000,
                'expiry_date' => '2028-01-31',
                'low_stock_threshold' => 50,
            ],
            [
                'name' => 'Smecta thảo dược cầm tiêu chảy thú y',
                'unit' => 'gói',
                'stock_quantity' => 90,
                'import_price' => 6000,
                'sell_price' => 12000,
                'expiry_date' => '2027-08-31',
                'low_stock_threshold' => 20,
            ],
            [
                'name' => 'Nutri-plus Gel dinh dưỡng phục hồi',
                'unit' => 'tuýp',
                'stock_quantity' => 60,
                'import_price' => 160000,
                'sell_price' => 250000,
                'expiry_date' => '2027-09-30',
                'low_stock_threshold' => 15,
            ],

            // --- KÝ SINH TRÙNG, VE RẬN & DA LIỄU ---
            [
                'name' => 'NexGard Spectra 7.5-15kg (Đặc trị ve rận, giun tim)',
                'unit' => 'viên',
                'stock_quantity' => 40,
                'import_price' => 180000,
                'sell_price' => 260000,
                'expiry_date' => '2028-03-31',
                'low_stock_threshold' => 10,
            ],
            [
                'name' => 'Bravecto 10-20kg (Trị ve rận kéo dài 3 tháng)',
                'unit' => 'viên',
                'stock_quantity' => 25,
                'import_price' => 450000,
                'sell_price' => 620000,
                'expiry_date' => '2028-05-31',
                'low_stock_threshold' => 8,
            ],
            [
                'name' => 'Tẩy giun Drontal Plus cho chó',
                'unit' => 'viên',
                'stock_quantity' => 110,
                'import_price' => 25000,
                'sell_price' => 45000,
                'expiry_date' => '2028-02-28',
                'low_stock_threshold' => 20,
            ],
            [
                'name' => 'Tẩy giun Milbemax cho mèo',
                'unit' => 'viên',
                'stock_quantity' => 85,
                'import_price' => 55000,
                'sell_price' => 90000,
                'expiry_date' => '2027-11-30',
                'low_stock_threshold' => 15,
            ],
            [
                'name' => 'Dung dịch rửa tai Otoclean sát khuẩn',
                'unit' => 'lọ',
                'stock_quantity' => 50,
                'import_price' => 75000,
                'sell_price' => 130000,
                'expiry_date' => '2027-08-31',
                'low_stock_threshold' => 10,
            ],
            [
                'name' => 'Xịt trị nấm da Fungikur',
                'unit' => 'chai',
                'stock_quantity' => 35,
                'import_price' => 90000,
                'sell_price' => 150000,
                'expiry_date' => '2027-06-30',
                'low_stock_threshold' => 10,
            ],
            [
                'name' => 'Thuốc nhỏ mắt Tobramycin 0.3%',
                'unit' => 'lọ',
                'stock_quantity' => 65,
                'import_price' => 22000,
                'sell_price' => 45000,
                'expiry_date' => '2027-05-31',
                'low_stock_threshold' => 15,
            ],

            // --- BỔ TRỢ, VITAMIN & CANXI ---
            [
                'name' => 'Vitamin B Complex tổng hợp',
                'unit' => 'viên',
                'stock_quantity' => 15, // Test cảnh báo tồn ít
                'import_price' => 600,
                'sell_price' => 1500,
                'expiry_date' => '2026-10-31',
                'low_stock_threshold' => 30,
            ],
            [
                'name' => 'Canxi Nano Canxi Milk bổ sung xương khớp',
                'unit' => 'hộp',
                'stock_quantity' => 30,
                'import_price' => 120000,
                'sell_price' => 190000,
                'expiry_date' => '2027-12-31',
                'low_stock_threshold' => 10,
            ],
            [
                'name' => 'Dầu cá Omega 3-6 Megaderm dưỡng lông',
                'unit' => 'gói',
                'stock_quantity' => 140,
                'import_price' => 10000,
                'sell_price' => 18000,
                'expiry_date' => '2027-10-31',
                'low_stock_threshold' => 25,
            ],
        ];

        foreach ($medicines as $medData) {
            $batches = $medData['batches'] ?? null;
            unset($medData['batches']);

            $medicine = Medicine::updateOrCreate(
                ['name' => $medData['name']],
                $medData
            );

            // Tạo các lô thuốc mẫu
            if (!empty($batches)) {
                foreach ($batches as $b) {
                    $medicine->batches()->updateOrCreate(
                        ['batch_number' => $b['batch_number']],
                        [
                            'quantity'           => $b['qty'],
                            'remaining_quantity' => $b['qty'],
                            'import_price'       => $medicine->import_price,
                            'expiry_date'        => $b['expiry'],
                        ]
                    );
                }
                $medicine->recalculateStockAndEarliestExpiry();
            } elseif ($medicine->batches()->count() === 0 && $medicine->stock_quantity > 0) {
                $medicine->batches()->create([
                    'batch_number'       => 'LO-' . date('Ym') . '-' . str_pad($medicine->id, 3, '0', STR_PAD_LEFT),
                    'quantity'           => $medicine->stock_quantity,
                    'remaining_quantity' => $medicine->stock_quantity,
                    'import_price'       => $medicine->import_price,
                    'expiry_date'        => $medicine->expiry_date,
                ]);
            }
        }

        $this->command->info('Expanded medicine inventory and batches seeded successfully!');
    }
}
