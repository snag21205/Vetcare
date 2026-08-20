<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'appointment_id'       => 'nullable|exists:appointments,id',
            'pet_id'               => 'required|exists:pets,id',
            'doctor_id'            => 'required|exists:users,id',
            'symptoms'             => 'required|string|max:2000',
            'diagnosis'            => 'required|string|max:2000',
            'weight_at_visit'      => 'nullable|numeric|min:0.1|max:200',
            'temperature_at_visit' => 'nullable|numeric|min:30|max:45',
            'note'                 => 'nullable|string',
            'status'               => 'required|in:in_progress,completed',

            // Validate mảng dịch vụ đi kèm
            'services'                    => 'nullable|array',
            'services.*'                  => 'exists:services,id',

            // Validate mảng đơn thuốc
            'prescriptions'               => 'nullable|array',
            'prescriptions.*.medicine_id' => 'required|exists:medicines,id',
            'prescriptions.*.dosage'      => 'required|string|max:255',
            'prescriptions.*.quantity'    => 'required|integer|min:1',
            'prescriptions.*.days'        => 'nullable|integer|min:1',
            'prescriptions.*.note'        => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'pet_id.required'                      => 'Vui lòng chọn thú cưng.',
            'symptoms.required'                    => 'Vui lòng nhập triệu chứng lâm sàng.',
            'diagnosis.required'                   => 'Vui lòng nhập chẩn đoán.',
            'prescriptions.*.medicine_id.required' => 'Vui lòng chọn loại thuốc.',
            'prescriptions.*.dosage.required'      => 'Vui lòng nhập liều dùng.',
            'prescriptions.*.quantity.required'    => 'Vui lòng nhập số lượng thuốc.',
            'prescriptions.*.quantity.min'         => 'Số lượng thuốc tối thiểu là 1.',
        ];
    }
}
