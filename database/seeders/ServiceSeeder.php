<?php

namespace Database\Seeders;

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
            // --- KHÁM & CẤP CỨU ---
            ['name' => 'Phí khám tổng quát',                 'price' => 150000, 'description' => 'Khám lâm sàng toàn diện, kiểm tra tim phổi, nhiệt độ, niêm mạc (tự động gắn vào phiếu khám)'],
            ['name' => 'Khám cấp cứu ngoài giờ',             'price' => 250000, 'description' => 'Xử lý các ca ngộ độc, khó thở, chấn thương ngoài giờ làm việc'],
            ['name' => 'Tái khám định kỳ theo hẹn',          'price' =>  80000, 'description' => 'Đánh giá mức độ đáp ứng thuốc và tiến triển sau điều trị'],

            // --- XÉT NGHIỆM & CHẨN ĐOÁN HÌNH ẢNH ---
            ['name' => 'Xét nghiệm tổng phân tích tế bào máu (CBC)', 'price' => 250000, 'description' => 'Kiểm tra hồng cầu, bạch cầu, tiểu cầu, phát hiện nhiễm trùng và thiếu máu'],
            ['name' => 'Xét nghiệm sinh hóa máu tổng quát (12 chỉ số)', 'price' => 450000, 'description' => 'Đánh giá chức năng gan (ALT, AST), thận (BUN, Creatinine), đường huyết'],
            ['name' => 'Chụp X-quang kỹ thuật số tim phổi',          'price' => 300000, 'description' => 'Chụp 2 tư thế lồng ngực đánh giá viêm phổi, tràn dịch, dị vật đường thở'],
            ['name' => 'Chụp X-quang xương khớp',                   'price' => 300000, 'description' => 'Kiểm tra gãy xương, trật khớp bánh chè, thoái hóa khớp'],
            ['name' => 'Siêu âm tổng quát ổ bụng màu',             'price' => 350000, 'description' => 'Khảo sát bàng quang, sỏi thận, dạ dày, ruột non, gan mật'],
            ['name' => 'Siêu âm thai Doppler',                      'price' => 250000, 'description' => 'Xác định số lượng thai, nhịp tim thai và ngày dự sinh'],
            
            // --- TEST NHANH BỆNH TRUYỀN NHIỄM ---
            ['name' => 'Test nhanh kháng nguyên Parvo virus (chó)', 'price' => 200000, 'description' => 'Phát hiện nhanh virus Parvo gây viêm ruột xuất huyết trong 10 phút'],
            ['name' => 'Test nhanh kháng nguyên Care / Distemper (chó)', 'price' => 200000, 'description' => 'Phát hiện virus sài sốt Care ở chó'],
            ['name' => 'Test nhanh virus Giảm bạch cầu FPV (mèo)',  'price' => 200000, 'description' => 'Phát hiện bệnh viêm ruột truyền nhiễm / giảm bạch cầu ở mèo'],
            ['name' => 'Test nhanh phức hợp hô hấp FHV-1 & FCV (mèo)', 'price' => 250000, 'description' => 'Phát hiện virus Herpes và Calici gây loét miệng chảy mũi ở mèo'],
            ['name' => 'Soi tươi da tìm Nấm và Ký sinh trùng Demodex', 'price' => 100000, 'description' => 'Lấy mẫu biểu bì soi kính hiển vi tìm ve bọ rận và nấm da'],
            ['name' => 'Soi đèn Wood kiểm tra nấm phát quang',      'price' =>  80000, 'description' => 'Chiếu đèn cực tím phát hiện nấm da Microsporum canis'],

            // --- TIÊM PHÒNG & PHÒNG BỆNH ---
            ['name' => 'Tiêm phòng Vaccine Dại (Rabies)',           'price' => 150000, 'description' => 'Vaccine phòng bệnh dại định kỳ hàng năm kèm sổ theo dõi'],
            ['name' => 'Tiêm phòng Vaccine 7 bệnh cho chó (Vanguard Plus 5/L)', 'price' => 350000, 'description' => 'Phòng Care, Parvo, Viêm gan, Ho cũi chó, Cúm, Leptospira'],
            ['name' => 'Tiêm phòng Vaccine 4 bệnh cho mèo (Felocell 4)', 'price' => 380000, 'description' => 'Phòng Giảm bạch cầu, Viêm mũi khí quản, Calici và Chlamydia'],

            // --- PHẪU THUẬT & ĐIỀU TRỊ THỦ THUẬT ---
            ['name' => 'Cạo vôi răng sóng siêu âm & Đánh bóng',     'price' => 350000, 'description' => 'Làm sạch mảng bám cao răng, khử mùi hôi miệng dưới tiền mê nhẹ'],
            ['name' => 'Triệt sản mèo đực / chó đực (dưới 5kg)',    'price' => 450000, 'description' => 'Phẫu thuật triệt sản an toàn, hồi phục nhanh trong ngày'],
            ['name' => 'Triệt sản mèo cái / chó cái (dưới 5kg)',    'price' => 650000, 'description' => 'Phẫu thuật mổ nội soi mini đường bụng, thẩm mỹ cao'],
            ['name' => 'Khâu may xử lý vết thương rách da phần mềm', 'price' => 300000, 'description' => 'Gây tê cục bộ, sát trùng và may chỉ thẩm mỹ vết rách'],
            ['name' => 'Truyền dịch tĩnh mạch & Lưu chuồng điều trị (ngày)', 'price' => 200000, 'description' => 'Truyền dịch bù nước điện giải, theo dõi sinh hiệu và chăm sóc nội trú'],
            ['name' => 'Xông khí dung (Nebulization) đường thở',    'price' => 100000, 'description' => 'Xông thuốc giãn phế quản và kháng sinh thông đường hô hấp'],

            // --- SPA & GROOMING ---
            ['name' => 'Vệ sinh tai & Vắt tuyến hôi',               'price' =>  60000, 'description' => 'Rửa sạch ráy tai và nặn sạch túi tuyến hôi hậu môn'],
            ['name' => 'Cắt mài móng & Cạo lông bàn chân',          'price' =>  70000, 'description' => 'Cắt gọn móng tránh ghim thịt và vệ sinh đệm bàn chân'],
            ['name' => 'Tắm sấy khử mùi & Dưỡng lông thú cưng',    'price' => 150000, 'description' => 'Tắm sữa tắm chuyên dụng, sấy phồng và chải tơi lông'],
            ['name' => 'Grooming cắt tỉa lông tạo kiểu trọn gói',   'price' => 350000, 'description' => 'Cắt tỉa tạo kiểu theo yêu cầu (gấu bông, poodle style...)'],
        ];

        foreach ($services as $svc) {
            Service::updateOrCreate(
                ['name' => $svc['name']],
                $svc
            );
        }

        $this->command->info('Expanded veterinary services seeded successfully!');
    }
}
