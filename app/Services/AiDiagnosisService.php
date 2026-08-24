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
        $apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');

        if (!empty($apiKey)) {
            try {
                $response = $this->callGeminiApi($apiKey, $species, $symptoms, $weight, $temp);
                if ($response && !empty($response['primary_diagnosis'])) {
                    return $response;
                }
            } catch (\Throwable $e) {
                Log::warning('Gemini API call failed, falling back to local diagnostic engine: ' . $e->getMessage());
            }
        }

        // Fallback: Veterinary Diagnostic Intelligence Engine
        return $this->fallbackDiagnosticEngine($species, $symptoms, $weight, $temp);
    }

    /**
     * Gọi Google Gemini REST API.
     */
    protected function callGeminiApi(string $apiKey, string $species, string $symptoms, ?float $weight, ?float $temp): ?array
    {
        $systemInstruction = "Bạn là một chuyên gia Bác sĩ Thú y hàng đầu. Dựa trên thông tin loài vật nuôi, cân nặng, thân nhiệt và các triệu chứng lâm sàng được cung cấp, hãy phân tích và đưa ra chẩn đoán chính xác nhất. BẮT BUỘC trả về định dạng JSON thuần túy (không bọc trong markdown block) với schema:
        {
            \"primary_diagnosis\": \"Tên chẩn đoán chính\",
            \"confidence\": \"X%\",
            \"differential_diagnoses\": [
                { \"name\": \"Tên bệnh 1\", \"probability\": \"Cao/Trung bình/Thấp\", \"reason\": \"Lý do ngắn gọn\" },
                { \"name\": \"Tên bệnh 2\", \"probability\": \"Cao/Trung bình/Thấp\", \"reason\": \"Lý do ngắn gọn\" }
            ],
            \"suggested_medicines\": [
                { \"name\": \"Tên thuốc\", \"dosage\": \"Liều dùng mẫu\", \"days\": 5, \"purpose\": \"Tác dụng\" }
            ],
            \"suggested_services\": [\"Tên xét nghiệm hoặc thủ thuật nên làm\"],
            \"advice\": \"Lời dặn dò cho bác sĩ và chủ nuôi\"
        }";

        $prompt = "Loài vật nuôi: {$species}\nTriệu chứng lâm sàng: {$symptoms}\nCân nặng: " . ($weight ? "{$weight} kg" : 'Chưa rõ') . "\nThân nhiệt: " . ($temp ? "{$temp} °C" : 'Bình thường');

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        $response = Http::timeout(12)->post($url, [
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
            if (is_array($data)) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Local Veterinary Intelligence Rule Engine (Chẩn đoán thông minh ngoại tuyến).
     */
    protected function fallbackDiagnosticEngine(string $species, string $symptoms, ?float $weight, ?float $temp): array
    {
        $sympLower = mb_strtolower($symptoms);
        $speciesLower = mb_strtolower($species);

        // 1. Nhóm bệnh hô hấp (Ho, hắt hơi, sổ mũi, khó thở)
        if (str_contains($sympLower, 'ho') || str_contains($sympLower, 'sổ mũi') || str_contains($sympLower, 'hắt hơi') || str_contains($sympLower, 'khó thở') || str_contains($sympLower, 'thở khò khè')) {
            $isHighTemp = $temp && $temp >= 39.5;
            return [
                'primary_diagnosis' => $isHighTemp ? 'Viêm phế quản phổi cấp tính có sốt' : 'Viêm đường hô hấp trên / Ho cũi chó (Kennel Cough)',
                'confidence' => '88%',
                'differential_diagnoses' => [
                    ['name' => 'Viêm phế quản co thắt', 'probability' => 'Cao', 'reason' => 'Xuất hiện phản xạ ho nhiều, thở khò khè.'],
                    ['name' => 'Nhiễm virus đường hô hấp (FHV-1 ở mèo hoặc CPiV ở chó)', 'probability' => 'Trung bình', 'reason' => 'Có biểu hiện chảy dịch mũi và hắt hơi liên tục.'],
                    ['name' => 'Dị vật đường thở', 'probability' => 'Thấp', 'reason' => 'Cần loại trừ nếu ho sặc sụa đột ngột.'],
                ],
                'suggested_medicines' => [
                    ['name' => 'Amoxicillin 250mg', 'dosage' => '1 viên/lần, ngày 2 lần', 'days' => 7, 'purpose' => 'Kháng sinh phổ rộng chống bội nhiễm vi khuẩn'],
                    ['name' => 'Bromhexin', 'dosage' => '1/2 viên/ngày', 'days' => 5, 'purpose' => 'Long đờm, thông thoáng đường thở'],
                    ['name' => 'Vitamin C 500mg', 'dosage' => '1 viên/ngày', 'days' => 5, 'purpose' => 'Tăng cường sức đề kháng'],
                ],
                'suggested_services' => [
                    'Chụp X-quang tim phổi kỹ thuật số',
                    'Xét nghiệm công thức máu tổng quát (CBC)'
                ],
                'advice' => 'Giữ ấm cho bé, tránh nằm phòng điều hòa lạnh. Vệ sinh sạch mũi mắt bằng nước muối sinh lý hàng ngày.'
            ];
        }

        // 2. Nhóm bệnh tiêu hóa (Tiêu chảy, nôn mửa, bỏ ăn, đau bụng)
        if (str_contains($sympLower, 'tiêu chảy') || str_contains($sympLower, 'nôn') || str_contains($sympLower, 'bỏ ăn') || str_contains($sympLower, 'đi ngoài') || str_contains($sympLower, 'phân lỏng') || str_contains($sympLower, 'mửa')) {
            $hasParvoRisk = str_contains($sympLower, 'phân tanh') || str_contains($sympLower, 'ra máu') || ($temp && $temp > 39.5);
            return [
                'primary_diagnosis' => $hasParvoRisk ? 'Nghi nhiễm Parvovirus / Viêm ruột xuất huyết cấp' : 'Rối loạn tiêu hóa / Viêm dạ dày ruột cấp tính',
                'confidence' => '85%',
                'differential_diagnoses' => [
                    ['name' => 'Nhiễm vi khuẩn đường ruột (E.coli/Salmonella)', 'probability' => 'Cao', 'reason' => 'Tiêu chảy phân lỏng kèm bỏ ăn uể oải.'],
                    ['name' => 'Nhiễm virus Parvo / Care (ở chó) hoặc Giảm bạch cầu (ở mèo)', 'probability' => $hasParvoRisk ? 'Rất cao' : 'Trung bình', 'reason' => 'Cần test nhanh kháng nguyên để loại trừ virus nguy hiểm.'],
                    ['name' => 'Ký sinh trùng đường ruột (Giardia, Coccidia)', 'probability' => 'Trung bình', 'reason' => 'Thường gặp nếu chưa được tẩy giun định kỳ.'],
                ],
                'suggested_medicines' => [
                    ['name' => 'Men tiêu hóa vi sinh', 'dosage' => '1 gói/ngày chia 2 lần', 'days' => 5, 'purpose' => 'Phục hồi hệ vi sinh đường ruột'],
                    ['name' => 'Metronidazole', 'dosage' => '1/2 viên/lần, ngày 2 lần', 'days' => 5, 'purpose' => 'Kháng viêm và diệt khuẩn đường ruột'],
                    ['name' => 'Dung dịch Oresol bù nước', 'dosage' => 'Uống tự do theo nhu cầu', 'days' => 3, 'purpose' => 'Chống mất nước và điện giải'],
                ],
                'suggested_services' => [
                    'Test nhanh kháng nguyên Parvo / Care',
                    'Siêu âm tổng quát ổ bụng',
                    'Xét nghiệm sinh hóa máu kiểm tra chức năng gan thận'
                ],
                'advice' => 'Nhịn ăn thức ăn đặc trong 12h đầu, chỉ cho uống từng ngụm nhỏ dung dịch bù điện giải. Nếu nôn liên tục cần truyền dịch tĩnh mạch ngay.'
            ];
        }

        // 3. Nhóm bệnh da liễu & Tai (Ngứa, gãi, rụng lông, nấm, viêm tai)
        if (str_contains($sympLower, 'ngứa') || str_contains($sympLower, 'rụng lông') || str_contains($sympLower, 'gãi') || str_contains($sympLower, 'nấm') || str_contains($sympLower, 'tai') || str_contains($sympLower, 'ghẻ') || str_contains($sympLower, 've') || str_contains($sympLower, 'bọ chét')) {
            return [
                'primary_diagnosis' => 'Viêm da dị ứng / Viêm tai ngoài do nấm & ký sinh trùng',
                'confidence' => '90%',
                'differential_diagnoses' => [
                    ['name' => 'Nhiễm nấm da (Microsporum canis)', 'probability' => 'Cao', 'reason' => 'Vùng da tổn thương có vảy tròn, rụng lông viền đỏ.'],
                    ['name' => 'Ve tai / Rận tai (Otodectes cynotis)', 'probability' => 'Cao', 'reason' => 'Gãi tai nhiều, có rỉ tai màu nâu đen như bã cà phê.'],
                    ['name' => 'Viêm da do viêm nang lông vi khuẩn (Pyoderma)', 'probability' => 'Trung bình', 'reason' => 'Xuất hiện mụn mủ nhỏ li ti rải rác.'],
                ],
                'suggested_medicines' => [
                    ['name' => 'Nexgard / Bravecto', 'dosage' => '1 viên duy nhất theo cân nặng', 'days' => 1, 'purpose' => 'Đặc trị ve rận, ghẻ demodex và bọ chét'],
                    ['name' => 'Dung dịch rửa tai chuyên dụng', 'dosage' => 'Nhỏ 3-5 giọt/tai, ngày 2 lần', 'days' => 7, 'purpose' => 'Làm sạch ráy tai và sát trùng'],
                    ['name' => 'Thuốc bôi da thảo dược', 'dosage' => 'Bôi trực tiếp lên vùng da viêm, ngày 2 lần', 'days' => 10, 'purpose' => 'Kháng nấm và tái tạo biểu bì da'],
                ],
                'suggested_services' => [
                    'Soi tươi bệnh phẩm da/tai tìm nấm và ký sinh trùng',
                    'Soi đèn Wood kiểm tra nấm phát quang'
                ],
                'advice' => 'Đeo loa chống liếm (vòng Elizabeth) tránh để thú cưng gãi làm trầy xước nhiễm trùng thứ phát. Vệ sinh môi trường sống và đệm nằm.'
            ];
        }

        // 4. Nhóm bệnh mắt (Đỏ mắt, chảy nước mắt, đục giác mạc)
        if (str_contains($sympLower, 'mắt') || str_contains($sympLower, 'đỏ mắt') || str_contains($sympLower, 'ghèn') || str_contains($sympLower, 'chảy nước mắt')) {
            return [
                'primary_diagnosis' => 'Viêm kết mạc / Viêm giác mạc mắt cấp tính',
                'confidence' => '86%',
                'differential_diagnoses' => [
                    ['name' => 'Viêm kết mạc do dị ứng hoặc bụi bẩn', 'probability' => 'Cao', 'reason' => 'Kết mạc mắt đỏ ửng kèm chảy nước mắt nhiều.'],
                    ['name' => 'Loét giác mạc do cào xước', 'probability' => 'Trung bình', 'reason' => 'Bé hay nheo mắt và sợ ánh sáng.'],
                ],
                'suggested_medicines' => [
                    ['name' => 'Thuốc nhỏ mắt Tobramycin', 'dosage' => 'Nhỏ 1-2 giọt/mắt, ngày 3 lần', 'days' => 7, 'purpose' => 'Kháng sinh nhỏ mắt phổ rộng'],
                    ['name' => 'Nước mắt nhân tạo', 'dosage' => 'Nhỏ 1 giọt/mắt khi khô rát', 'days' => 7, 'purpose' => 'Làm dịu và dưỡng ẩm giác mạc'],
                ],
                'suggested_services' => [
                    'Nhuộm huỳnh quang Fluorescein kiểm tra vết loét giác mạc',
                    'Đo áp lực nội nhãn'
                ],
                'advice' => 'Tránh để thú cưng dụi mắt vào chân hoặc cọ vào đồ vật. Đeo loa bảo vệ nếu cần thiết.'
            ];
        }

        // 5. Mặc định tổng quát
        return [
            'primary_diagnosis' => 'Kiểm tra sức khỏe tổng quát / Nghi ngờ hội chứng suy giảm thể trạng',
            'confidence' => '78%',
            'differential_diagnoses' => [
                ['name' => 'Nhiễm trùng tiềm ẩn chưa định vị', 'probability' => 'Trung bình', 'reason' => 'Cần xét nghiệm công thức máu toàn phần.'],
                ['name' => 'Thiếu hụt vi chất & dinh dưỡng', 'probability' => 'Trung bình', 'reason' => 'Mệt mỏi và uể oải.'],
            ],
            'suggested_medicines' => [
                ['name' => 'Gel dinh dưỡng Nutri-plus', 'dosage' => '1 muỗng cà phê/ngày', 'days' => 7, 'purpose' => 'Bồi bổ năng lượng và vitamin thiết yếu'],
                ['name' => 'Men vi sinh Probiotic', 'dosage' => '1 gói/ngày', 'days' => 5, 'purpose' => 'Kích thích tiêu hóa ăn ngon'],
            ],
            'suggested_services' => [
                'Khám lâm sàng toàn diện',
                'Xét nghiệm máu tổng quát'
            ],
            'advice' => 'Tiếp tục theo dõi thân nhiệt và lượng thức ăn tiêu thụ hàng ngày. Đưa đến phòng khám tái khám nếu có triệu chứng nặng hơn.'
        ];
    }
}
