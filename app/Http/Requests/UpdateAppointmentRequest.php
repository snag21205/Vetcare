<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pet_id'       => 'required|exists:pets,id',
            'doctor_id'    => 'nullable|exists:users,id',
            'scheduled_at' => 'required|date',
            'reason'       => 'required|string|max:500',
            'note'         => 'nullable|string',
            'notes'        => 'nullable|string',
            'status'       => 'sometimes|in:pending,confirmed,checked_in,completed,cancelled,no_show',
        ];
    }

    public function messages(): array
    {
        return [
            'pet_id.required'            => 'Vui lòng chọn vật nuôi.',
            'pet_id.exists'              => 'Vật nuôi không tồn tại.',
            'scheduled_at.required'      => 'Vui lòng chọn ngày giờ hẹn.',
            'reason.required'            => 'Vui lòng nhập lý do khám.',
        ];
    }
}
