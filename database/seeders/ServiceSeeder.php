<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Phí khám tổng quát',       'price' => 150000, 'description' => 'Phí khám cơ bản, tự động gắn vào mọi phiếu khám'],
            ['name' => 'Xét nghiệm máu tổng quát', 'price' => 250000, 'description' => 'CBC, sinh hóa máu'],
            ['name' => 'Chụp X-quang',             'price' => 300000, 'description' => null],
            ['name' => 'Siêu âm bụng',             'price' => 350000, 'description' => null],
            ['name' => 'Tiêm phòng Dại',           'price' => 200000, 'description' => 'Vaccine hàng năm'],
            ['name' => 'Tiêm phòng 7 bệnh (chó)',  'price' => 350000, 'description' => 'Vaccine 7-in-1'],
            ['name' => 'Cắt móng',                 'price' =>  50000, 'description' => null],
            ['name' => 'Tắm grooming cơ bản',      'price' => 150000, 'description' => null],
        ];

        foreach ($services as $svc) {
            Service::firstOrCreate(['name' => $svc['name']], $svc);
        }

        $this->command->info('Services seeded!');
    }
}
