<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Pet;
use App\Models\User;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Service;
use App\Models\Invoice;
use App\Models\ExpenseVoucher;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vet = User::role('vet')->first() ?? User::first();
        $admin = User::role('admin')->first() ?? User::first();

        // 1. KHÁCH HÀNG & THÚ CƯNG
        $c1 = Customer::firstOrCreate(['phone' => '0901234567'], [
            'name'    => 'Nguyễn Thị Mai',
            'email'   => 'mai.nguyen@gmail.com',
            'address' => '123 Đường Lê Lợi, Quận 1, TP.HCM',
        ]);
        $p1 = Pet::firstOrCreate(['customer_id' => $c1->id, 'name' => 'Mimi'], [
            'species'       => 'mèo',
            'breed'         => 'Mèo Anh Lông Ngắn (British Shorthair)',
            'date_of_birth' => '2023-03-15',
            'gender'        => 'female',
            'weight'        => 3.8,
        ]);
        $p2 = Pet::firstOrCreate(['customer_id' => $c1->id, 'name' => 'Bông'], [
            'species'       => 'chó',
            'breed'         => 'Poodle Tiny',
            'date_of_birth' => '2022-08-10',
            'gender'        => 'male',
            'weight'        => 4.2,
        ]);

        $c2 = Customer::firstOrCreate(['phone' => '0987654321'], [
            'name'    => 'Trần Văn Nam',
            'email'   => 'nam.tran@gmail.com',
            'address' => '456 Nguyễn Huệ, Quận 3, TP.HCM',
        ]);
        $p3 = Pet::firstOrCreate(['customer_id' => $c2->id, 'name' => 'Cún'], [
            'species'       => 'chó',
            'breed'         => 'Husky Siberian',
            'date_of_birth' => '2021-05-20',
            'gender'        => 'male',
            'weight'        => 22.5,
        ]);

        $c3 = Customer::firstOrCreate(['phone' => '0912345678'], [
            'name'    => 'Lê Hoàng Yến',
            'email'   => 'hoangyen.le@gmail.com',
            'address' => '789 Trần Hưng Đạo, Quận 5, TP.HCM',
        ]);
        $p4 = Pet::firstOrCreate(['customer_id' => $c3->id, 'name' => 'Milo'], [
            'species'       => 'chó',
            'breed'         => 'Corgi Pembroke',
            'date_of_birth' => '2023-01-12',
            'gender'        => 'male',
            'weight'        => 11.2,
        ]);
        $p5 = Pet::firstOrCreate(['customer_id' => $c3->id, 'name' => 'Kitty'], [
            'species'       => 'mèo',
            'breed'         => 'Mèo Ba Tư (Persian)',
            'date_of_birth' => '2022-11-05',
            'gender'        => 'female',
            'weight'        => 3.5,
        ]);

        $c4 = Customer::firstOrCreate(['phone' => '0933889900'], [
            'name'    => 'Phạm Minh Đức',
            'email'   => 'duc.pham@yahoo.com',
            'address' => '24 Nguyễn Thị Minh Khai, Quận 1, TP.HCM',
        ]);
        $p6 = Pet::firstOrCreate(['customer_id' => $c4->id, 'name' => 'Lucky'], [
            'species'       => 'chó',
            'breed'         => 'Golden Retriever',
            'date_of_birth' => '2022-04-18',
            'gender'        => 'male',
            'weight'        => 28.0,
        ]);

        $c5 = Customer::firstOrCreate(['phone' => '0977112233'], [
            'name'    => 'Đỗ Thu Hà',
            'email'   => 'thuha.do@gmail.com',
            'address' => '88 Võ Văn Tần, Quận 3, TP.HCM',
        ]);
        $p7 = Pet::firstOrCreate(['customer_id' => $c5->id, 'name' => 'Simba'], [
            'species'       => 'mèo',
            'breed'         => 'Mèo Mướp Ta',
            'date_of_birth' => '2023-06-20',
            'gender'        => 'male',
            'weight'        => 4.0,
        ]);

        // 2. LỊCH HẸN HÔM NAY VÀ NGÀY MAI
        Appointment::updateOrCreate([
            'pet_id'       => $p1->id,
            'scheduled_at' => today()->setHour(9)->setMinute(0),
        ], [
            'doctor_id'  => $vet->id,
            'created_by' => $admin->id,
            'status'     => 'checked_in',
            'reason'     => 'Kiểm tra mắt bị đỏ chảy gỉ và hắt hơi',
            'note'       => 'Khách đã có mặt tại sảnh chờ',
        ]);

        Appointment::updateOrCreate([
            'pet_id'       => $p4->id,
            'scheduled_at' => today()->setHour(14)->setMinute(30),
        ], [
            'doctor_id'  => $vet->id,
            'created_by' => $admin->id,
            'status'     => 'confirmed',
            'reason'     => 'Tiêm phòng vaccine định kỳ 7 bệnh',
            'note'       => 'Đã gọi điện xác nhận buổi sáng',
        ]);

        Appointment::updateOrCreate([
            'pet_id'       => $p6->id,
            'scheduled_at' => today()->addDay()->setHour(10)->setMinute(0),
        ], [
            'doctor_id'  => $vet->id,
            'created_by' => $admin->id,
            'status'     => 'confirmed',
            'reason'     => 'Grooming cắt tỉa lông và tắm trị liệu ve nấm',
            'note'       => 'Chó lớn hiếu động',
        ]);

        Appointment::updateOrCreate([
            'pet_id'       => $p7->id,
            'scheduled_at' => today()->addDay()->setHour(15)->setMinute(0),
        ], [
            'doctor_id'  => $vet->id,
            'created_by' => $admin->id,
            'status'     => 'pending',
            'reason'     => 'Khám da bị rụng lông thành mảng tròn nghi nấm',
            'note'       => 'Đặt qua hotline',
        ]);

        // 3. MỘT SỐ BỆNH ÁN LỊCH SỬ MẪU (Cho bé Bông & Cún)
        $medAugmentin = Medicine::where('name', 'like', '%Amoxicillin%')->first();
        $medBromhexin = Medicine::where('name', 'like', '%Bromhexin%')->first();
        $medNexgard   = Medicine::where('name', 'like', '%NexGard%')->first();
        $svExam       = Service::where('name', 'like', '%Phí khám tổng quát%')->first();
        $svBlood      = Service::where('name', 'like', '%Xét nghiệm%')->first();
        $svXray       = Service::where('name', 'like', '%X-quang%')->first();

        // Bệnh án cũ 1: Bé Bông bị viêm phế quản
        $rec1 = MedicalRecord::updateOrCreate([
            'pet_id'     => $p2->id,
            'created_at' => now()->subDays(15),
        ], [
            'doctor_id'            => $vet->id,
            'symptoms'             => 'Ho khan nhiều về đêm, thở khò khè, sốt nhẹ 39.1 độ, vẫn ăn uống được',
            'diagnosis'            => 'Viêm phế quản co thắt cấp tính',
            'weight_at_visit'      => 4.2,
            'temperature_at_visit' => 39.1,
            'note'                 => 'Uống thuốc đúng giờ, kiêng nước lạnh và nằm phòng điều hòa lạnh',
            'status'               => 'completed',
        ]);

        if ($rec1->prescriptions()->count() === 0 && $medAugmentin && $medBromhexin) {
            $rec1->prescriptions()->create([
                'medicine_id' => $medAugmentin->id,
                'dosage'      => '1/2 viên/lần, ngày 2 lần sau ăn',
                'quantity'    => 7,
                'days'        => 7,
                'note'        => 'Kháng sinh chống viêm',
            ]);
            $rec1->prescriptions()->create([
                'medicine_id' => $medBromhexin->id,
                'dosage'      => '1/2 viên/lần, ngày 1 lần',
                'quantity'    => 5,
                'days'        => 5,
                'note'        => 'Long đờm giảm ho',
            ]);
        }

        if ($rec1->services()->count() === 0 && $svExam && $svXray) {
            $rec1->services()->attach($svExam->id, ['price' => $svExam->price]);
            $rec1->services()->attach($svXray->id, ['price' => $svXray->price]);
        }

        // Bệnh án cũ 2: Bé Cún điều trị ve rận và viêm da
        $rec2 = MedicalRecord::updateOrCreate([
            'pet_id'     => $p3->id,
            'created_at' => now()->subDays(30),
        ], [
            'doctor_id'            => $vet->id,
            'symptoms'             => 'Ngứa gãi tai dữ dội, rụng lông viền tai, có ráy tai màu nâu đen như bã cà phê',
            'diagnosis'            => 'Viêm tai ngoài do ve tai (Otodectes cynotis) kèm viêm da dị ứng',
            'weight_at_visit'      => 22.0,
            'temperature_at_visit' => 38.6,
            'note'                 => 'Đeo vòng chống liếm trong 5 ngày, vệ sinh tai bằng dung dịch chuyên dụng 2 lần/ngày',
            'status'               => 'completed',
        ]);

        if ($rec2->prescriptions()->count() === 0 && $medNexgard) {
            $rec2->prescriptions()->create([
                'medicine_id' => $medNexgard->id,
                'dosage'      => '1 viên duy nhất uống kèm thức ăn',
                'quantity'    => 1,
                'days'        => 1,
                'note'        => 'Đặc trị ve rận và bọ chét',
            ]);
        }

        if ($rec2->services()->count() === 0 && $svExam) {
            $rec2->services()->attach($svExam->id, ['price' => $svExam->price]);
        }

        // 4. MỘT SỐ PHIẾU CHI MẪU (Sổ quỹ phòng khám)
        ExpenseVoucher::firstOrCreate([
            'description' => 'Nhập lô kháng sinh và men tiêu hóa thú y từ Dược phẩm Thú y TW',
        ], [
            'category'     => 'medicine_import',
            'amount'       => 2850000,
            'voucher_date' => today()->subDays(5),
            'created_by'   => $admin->id,
        ]);

        ExpenseVoucher::firstOrCreate([
            'description' => 'Chi trả tiền điện nước và cước internet tháng này',
        ], [
            'category'     => 'operational',
            'amount'       => 1650000,
            'voucher_date' => today()->subDays(10),
            'created_by'   => $admin->id,
        ]);

        $this->command->info('Full comprehensive demo data created successfully!');
    }
}
