<?php

namespace App\Http\Controllers;

use App\Services\AiDiagnosisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiDiagnosisController extends Controller
{
    public function __construct(protected AiDiagnosisService $aiService)
    {
        //
    }

    /**
     * Gợi ý chẩn đoán lâm sàng thông minh qua AI.
     */
    public function diagnose(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'species'     => ['required', 'string'],
            'symptoms'    => ['required', 'string', 'min:3'],
            'weight'      => ['nullable', 'numeric'],
            'temperature' => ['nullable', 'numeric'],
        ], [
            'species.required'  => 'Vui lòng chọn loài vật nuôi.',
            'symptoms.required' => 'Vui lòng nhập triệu chứng lâm sàng.',
            'symptoms.min'      => 'Triệu chứng cần có tối thiểu 3 ký tự để AI phân tích.',
        ]);

        $result = $this->aiService->suggestDiagnosis(
            species:  $validated['species'],
            symptoms: $validated['symptoms'],
            weight:   isset($validated['weight']) ? (float)$validated['weight'] : null,
            temp:     isset($validated['temperature']) ? (float)$validated['temperature'] : null,
        );

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }
}
