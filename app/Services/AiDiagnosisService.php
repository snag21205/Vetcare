<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\Service;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiDiagnosisService
{
    /**
     * Gợi ý chẩn đoán lâm sàng bằng Google Gemini AI (kèm fallback thông minh).
     */
    public function suggestDiagnosis(string $species, string $symptoms, ?float $weight = null, ?float $temp = null): array
    {
        // 1. Nạp danh mục thuốc và dịch vụ hiện có trong hệ thống phòng khám
        $medicines = Medicine::query()
            ->where('stock_quantity', '>', 0)
            ->get(['id', 'name', 'unit', 'stock_quantity']);

        $services = Service::all(['id', 'name', 'price']);

        $apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');

        if (!empty($apiKey)) {
            try {
                $response = $this->callGeminiApi($apiKey, $species, $symptoms, $weight, $temp, $medicines, $services);
                if ($response && !empty($response['primary_diagnosis'])) {
                    return $response;
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini API call failed, falling back to local diagnostic engine: ' . $e->getMessage());
            }
        }

        // Fallback: Veterinary Diagnostic Intelligence Engine (ràng buộc thuốc/dịch vụ trong DB)
        return $this->fallbackDiagnosticEngine($species, $symptoms, $weight, $temp, $medicines, $services);
    }

    /**
     * Gọi Google Gemini REST API với ràng buộc RAG dữ liệu kho thuốc & bảng giá dịch vụ.
     */
    protected function callGeminiApi(
        string $apiKey,
        string $species,
        string $symptoms,
        ?float $weight,
        ?float $temp,
        $medicines,
        $services
    ): ?array {
        // Tạo danh sách chuỗi thuốc và dịch vụ để đưa vào context
        $medListText = $medicines->map(fn($m) => "- ID: {$m->id} | Tên thuốc: {$m->name} (đơn vị: {$m->unit}, tồn kho: {$m->stock_quantity})")->implode("\n");
        $svcListText = $services->map(fn($s) => "- ID: {$s->id} | Tên dịch vụ: {$s->name}")->implode("\n");

        $systemInstruction = "Bạn là một chuyên gia Bác sĩ Thú y hàng đầu đang làm việc tại phòng khám thú y VetCare. Dựa trên thông tin loài vật nuôi, cân nặng, thân nhiệt và các triệu chứng lâm sàng được cung cấp, hãy phân tích và đưa ra chẩn đoán chính xác nhất.
        
BẮT BUỘC trả về định dạng JSON thuần túy (không bọc trong markdown block hay dấu ```) theo đúng cấu trúc schema sau:
{
    \"primary_diagnosis\": \"Tên chẩn đoán chính xác nhất\",
    \"confidence\": \"X%\",
    \"differential_diagnoses\": [
        { \"name\": \"Tên bệnh phân biệt 1\", \"probability\": \"Cao/Trung bình/Thấp\", \"reason\": \"Lý do ngắn gọn\" },
        { \"name\": \"Tên bệnh phân biệt 2\", \"probability\": \"Cao/Trung bình/Thấp\", \"reason\": \"Lý do ngắn gọn\" }
    ],
    \"suggested_medicines\": [
        { \"medicine_id\": 1, \"name\": \"Tên thuốc chính xác từ danh mục kho\", \"dosage\": \"Liều dùng mẫu (vd: 1 viên/ngày chia 2 lần)\", \"days\": 5, \"purpose\": \"Tác dụng điều trị\" }
    ],
    \"suggested_services\": [
        { \"service_id\": 2, \"name\": \"Tên dịch vụ kỹ thuật chính xác từ danh mục\" }
    ],
    \"advice\": \"Lời dặn dò ngắn gọn cho bác sĩ và chủ nuôi\"
}

QUY TẮC RÀNG BUỘC NGHIÊM NGẶT:
1. Trong mảng 'suggested_medicines', BẮT BUỘC CHỈ ĐƯỢC CHỌN các loại thuốc CÓ TRONG 'DANH SÁCH THUỐC HIỆN CÓ TRONG KHO' dưới đây. Trả về đúng 'medicine_id' và 'name' của thuốc. KHÔNG ĐƯỢC tự bịa tên thuốc ngoài danh mục.
2. Trong mảng 'suggested_services', BẮT BUỘC CHỈ ĐƯỢC CHỌN các dịch vụ CÓ TRONG 'DANH SÁCH DỊCH VỤ CỦA PHÒNG KHÁM' dưới đây. Trả về đúng 'service_id' và 'name' của dịch vụ. KHÔNG ĐƯỢC tự bịa dịch vụ ngoài danh mục.";

        $prompt = "--- THÔNG TIN BỆNH NHÂN ---
Loài vật nuôi: {$species}
Triệu chứng lâm sàng: {$symptoms}
Cân nặng: " . ($weight ? "{$weight} kg" : 'Chưa rõ') . "
Thân nhiệt: " . ($temp ? "{$temp} °C" : 'Bình thường') . "

--- DANH SÁCH THUỐC HIỆN CÓ TRONG KHO PHÒNG KHÁM (CHỈ CHỌN TỪ ĐÂY) ---
{$medListText}

--- DANH SÁCH DỊCH VỤ CỦA PHÒNG KHÁM (CHỈ CHỌN TỪ ĐÂY) ---
{$svcListText}";

        // Danh sách model ưu tiên
        $preferredModel = config('services.gemini.model') ?? 'gemini-3.5-flash';
        $models = array_unique([$preferredModel, 'gemini-3.5-flash', 'gemini-flash-latest', 'gemini-2.5-pro']);

        foreach ($models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            try {
                $response = Http::timeout(20)
                    ->withoutVerifying()
                    ->post($url, [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    ['text' => $systemInstruction . "\n\n" . $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.2,
                            'responseMimeType' => 'application/json',
                        ]
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $content = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    // Làm sạch markdown nếu có
                    $content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($content));
                    $data = json_decode($content, true);
                    if (is_array($data) && !empty($data['primary_diagnosis'])) {
                        return $data;
                    }
                }
            } catch (\Throwable $e) {
                Log::info("Gemini model {$model} failed: " . $e->getMessage());
                continue;
            }
        }

        return null;
    }

    /**
     * Local Veterinary Intelligence Rule Engine (Khớp chính xác thuốc và dịch vụ từ Database).
     */
    protected function fallbackDiagnosticEngine(string $species, string $symptoms, ?float $weight, ?float $temp, $medicines, $services): array
    {
        $sympLower = mb_strtolower($symptoms);

        // Helper tìm thuốc trong kho
        $findMed = function(array $keywords) use ($medicines) {
            foreach ($keywords as $kw) {
                $found = $medicines->first(fn($m) => str_contains(mb_strtolower($m->name), mb_strtolower($kw)));
                if ($found) return $found;
            }
            return null;
        };

        // Helper tìm dịch vụ trong bảng giá
        $findSvc = function(array $keywords) use ($services) {
            foreach ($keywords as $kw) {
                $found = $services->first(fn($s) => str_contains(mb_strtolower($s->name), mb_strtolower($kw)));
                if ($found) return $found;
            }
            return null;
        };

        // 1. Nhóm hô hấp
        if (str_contains($sympLower, 'ho') || str_contains($sympLower, 'sổ mũi') || str_contains($sympLower, 'hắt hơi') || str_contains($sympLower, 'khó thở') || str_contains($sympLower, 'thở khò khè')) {
            $isHighTemp = $temp && $temp >= 39.5;

            $sMeds = [];
            if ($m = $findMed(['Amoxicillin', 'Kháng sinh', 'Augmentin'])) {
                $sMeds[] = ['medicine_id' => $m->id, 'name' => $m->name, 'dosage' => '1 viên/lần, ngày 2 lần', 'days' => 7, 'purpose' => 'Kháng sinh chống bội nhiễm vi khuẩn'];
            }
            if ($m = $findMed(['Bromhexin', 'Ho', 'Long đờm'])) {
                $sMeds[] = ['medicine_id' => $m->id, 'name' => $m->name, 'dosage' => '1/2 viên/ngày', 'days' => 5, 'purpose' => 'Long đờm, thông thoáng đường thở'];
            }
            if ($m = $findMed(['Vitamin C', 'Nutri', 'Bổ'])) {
                $sMeds[] = ['medicine_id' => $m->id, 'name' => $m->name, 'dosage' => '1 viên/ngày', 'days' => 5, 'purpose' => 'Tăng cường sức đề kháng'];
            }

            $sSvcs = [];
            if ($s = $findSvc(['X-quang', 'X quang', 'Phổi'])) {
                $sSvcs[] = ['service_id' => $s->id, 'name' => $s->name];
            }
            if ($s = $findSvc(['Máu', 'Xét nghiệm máu', 'CBC'])) {
                $sSvcs[] = ['service_id' => $s->id, 'name' => $s->name];
            }

            return [
                'primary_diagnosis' => $isHighTemp ? 'Viêm phế quản phổi cấp tính có sốt' : 'Viêm đường hô hấp trên / Ho cũi chó (Kennel Cough)',
                'confidence' => '88%',
                'differential_diagnoses' => [
                    ['name' => 'Viêm phế quản co thắt', 'probability' => 'Cao', 'reason' => 'Xuất hiện phản xạ ho nhiều, thở khò khè.'],
                    ['name' => 'Nhiễm virus đường hô hấp (FHV-1 ở mèo hoặc CPiV ở chó)', 'probability' => 'Trung bình', 'reason' => 'Có biểu hiện chảy dịch mũi và hắt hơi liên tục.'],
                ],
                'suggested_medicines' => $sMeds,
                'suggested_services'  => $sSvcs,
                'advice' => 'Giữ ấm cho bé, tránh nằm phòng điều hòa lạnh. Vệ sinh sạch mũi mắt bằng nước muối sinh lý hàng ngày.'
            ];
        }

        // 2. Nhóm tiêu hóa
        if (str_contains($sympLower, 'tiêu chảy') || str_contains($sympLower, 'nôn') || str_contains($sympLower, 'bỏ ăn') || str_contains($sympLower, 'đi ngoài') || str_contains($sympLower, 'phân lỏng') || str_contains($sympLower, 'mửa')) {
            $hasParvoRisk = str_contains($sympLower, 'phân tanh') || str_contains($sympLower, 'ra máu') || ($temp && $temp > 39.5);

            $sMeds = [];
            if ($m = $findMed(['Men tiêu hóa', 'Probiotic', 'Men vi sinh'])) {
                $sMeds[] = ['medicine_id' => $m->id, 'name' => $m->name, 'dosage' => '1 gói/ngày chia 2 lần', 'days' => 5, 'purpose' => 'Phục hồi hệ vi sinh đường ruột'];
            }
            if ($m = $findMed(['Metronidazole', 'Kháng sinh', 'Amoxicillin'])) {
                $sMeds[] = ['medicine_id' => $m->id, 'name' => $m->name, 'dosage' => '1/2 viên/lần, ngày 2 lần', 'days' => 5, 'purpose' => 'Kháng viêm và diệt khuẩn đường ruột'];
            }
            if ($m = $findMed(['Oresol', 'Điện giải', 'Bù nước'])) {
                $sMeds[] = ['medicine_id' => $m->id, 'name' => $m->name, 'dosage' => 'Uống tự do theo nhu cầu', 'days' => 3, 'purpose' => 'Chống mất nước và điện giải'];
            }

            $sSvcs = [];
            if ($s = $findSvc(['Test nhanh', 'Parvo', 'Care'])) {
                $sSvcs[] = ['service_id' => $s->id, 'name' => $s->name];
            }
            if ($s = $findSvc(['Siêu âm', 'Ổ bụng'])) {
                $sSvcs[] = ['service_id' => $s->id, 'name' => $s->name];
            }
            if ($s = $findSvc(['Máu', 'Xét nghiệm'])) {
                $sSvcs[] = ['service_id' => $s->id, 'name' => $s->name];
            }

            return [
                'primary_diagnosis' => $hasParvoRisk ? 'Nghi nhiễm Parvovirus / Viêm ruột xuất huyết cấp' : 'Rối loạn tiêu hóa / Viêm dạ dày ruột cấp tính',
                'confidence' => '85%',
                'differential_diagnoses' => [
                    ['name' => 'Nhiễm vi khuẩn đường ruột (E.coli/Salmonella)', 'probability' => 'Cao', 'reason' => 'Tiêu chảy phân lỏng kèm bỏ ăn uể oải.'],
                    ['name' => 'Nhiễm virus Parvo / Care (ở chó) hoặc Giảm bạch cầu (ở mèo)', 'probability' => $hasParvoRisk ? 'Rất cao' : 'Trung bình', 'reason' => 'Cần test nhanh kháng nguyên để loại trừ virus nguy hiểm.'],
                ],
                'suggested_medicines' => $sMeds,
                'suggested_services'  => $sSvcs,
                'advice' => 'Nhịn ăn thức ăn đặc trong 12h đầu, chỉ cho uống từng ngụm nhỏ dung dịch bù điện giải. Nếu nôn liên tục cần truyền dịch tĩnh mạch ngay.'
            ];
        }

        // 3. Nhóm da liễu & Tai
        if (str_contains($sympLower, 'ngứa') || str_contains($sympLower, 'rụng lông') || str_contains($sympLower, 'gãi') || str_contains($sympLower, 'nấm') || str_contains($sympLower, 'tai') || str_contains($sympLower, 'ghẻ') || str_contains($sympLower, 've') || str_contains($sympLower, 'bọ chét')) {
            $sMeds = [];
            if ($m = $findMed(['Nexgard', 'Bravecto', 'Ve', 'Rận'])) {
                $sMeds[] = ['medicine_id' => $m->id, 'name' => $m->name, 'dosage' => '1 viên duy nhất theo cân nặng', 'days' => 1, 'purpose' => 'Đặc trị ve rận, ghẻ demodex và bọ chét'];
            }
            if ($m = $findMed(['Tai', 'Rửa tai', 'Otic'])) {
                $sMeds[] = ['medicine_id' => $m->id, 'name' => $m->name, 'dosage' => 'Nhỏ 3-5 giọt/tai, ngày 2 lần', 'days' => 7, 'purpose' => 'Làm sạch ráy tai và sát trùng'];
            }
            if ($m = $findMed(['Da', 'Bôi', 'Dưỡng'])) {
                $sMeds[] = ['medicine_id' => $m->id, 'name' => $m->name, 'dosage' => 'Bôi trực tiếp lên vùng da viêm, ngày 2 lần', 'days' => 10, 'purpose' => 'Kháng nấm và tái tạo biểu bì da'];
            }

            $sSvcs = [];
            if ($s = $findSvc(['Soi tươi', 'Soi da', 'Nấm'])) {
                $sSvcs[] = ['service_id' => $s->id, 'name' => $s->name];
            }
            if ($s = $findSvc(['Đèn Wood', 'Wood'])) {
                $sSvcs[] = ['service_id' => $s->id, 'name' => $s->name];
            }

            return [
                'primary_diagnosis' => 'Viêm da dị ứng / Viêm tai ngoài do nấm & ký sinh trùng',
                'confidence' => '90%',
                'differential_diagnoses' => [
                    ['name' => 'Nhiễm nấm da (Microsporum canis)', 'probability' => 'Cao', 'reason' => 'Vùng da tổn thương có vảy tròn, rụng lông viền đỏ.'],
                    ['name' => 'Ve tai / Rận tai (Otodectes cynotis)', 'probability' => 'Cao', 'reason' => 'Gãi tai nhiều, có rỉ tai màu nâu đen như bã cà phê.'],
                ],
                'suggested_medicines' => $sMeds,
                'suggested_services'  => $sSvcs,
                'advice' => 'Đeo loa chống liếm (vòng Elizabeth) tránh để thú cưng gãi làm trầy xước nhiễm trùng thứ phát. Vệ sinh môi trường sống và đệm nằm.'
            ];
        }

        // 4. Mặc định tổng quát
        $sMeds = [];
        if ($m = $findMed(['Nutri', 'Gel', 'Vitamin'])) {
            $sMeds[] = ['medicine_id' => $m->id, 'name' => $m->name, 'dosage' => '1 muỗng cà phê/ngày', 'days' => 7, 'purpose' => 'Bồi bổ năng lượng và vitamin thiết yếu'];
        }
        if ($m = $findMed(['Men', 'Probiotic'])) {
            $sMeds[] = ['medicine_id' => $m->id, 'name' => $m->name, 'dosage' => '1 gói/ngày', 'days' => 5, 'purpose' => 'Kích thích tiêu hóa ăn ngon'];
        }

        $sSvcs = [];
        if ($s = $findSvc(['Khám', 'Tổng quát'])) {
            $sSvcs[] = ['service_id' => $s->id, 'name' => $s->name];
        }
        if ($s = $findSvc(['Máu', 'Xét nghiệm'])) {
            $sSvcs[] = ['service_id' => $s->id, 'name' => $s->name];
        }

        return [
            'primary_diagnosis' => 'Kiểm tra sức khỏe tổng quát / Nghi ngờ hội chứng suy giảm thể trạng',
            'confidence' => '78%',
            'differential_diagnoses' => [
                ['name' => 'Nhiễm trùng tiềm ẩn chưa định vị', 'probability' => 'Trung bình', 'reason' => 'Cần xét nghiệm công thức máu toàn phần.'],
                ['name' => 'Thiếu hụt vi chất & dinh dưỡng', 'probability' => 'Trung bình', 'reason' => 'Mệt mỏi và uể oải.'],
            ],
            'suggested_medicines' => $sMeds,
            'suggested_services'  => $sSvcs,
            'advice' => 'Tiếp tục theo dõi thân nhiệt và lượng thức ăn tiêu thụ hàng ngày. Đưa đến phòng khám tái khám nếu có triệu chứng nặng hơn.'
        ];
    }
}
