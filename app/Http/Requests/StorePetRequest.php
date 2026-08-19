<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePetRequest extends FormRequest
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
            'customer_id'   => 'required|exists:customers,id',
            'name'          => 'required|string|max:255',
            'species'       => 'required|in:chó,mèo,khác',
            'breed'         => 'nullable|string|max:255',
            'gender'        => 'required|in:male,female,unknown',
            'date_of_birth' => 'nullable|date|before:today',
            'weight'        => 'nullable|numeric|min:0.1|max:100',
            'note'          => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Vui lòng chọn chủ thú cưng.',
            'customer_id.exists' => 'Khách hành không tồn tại.',
            'name.required' => 'Vui lòng nhập tên thú cưng.',
            'species.required' => 'Vui lòng chọn giống loài.',
            'species.in' => 'Loài phải là: chó, mèo hoặc khác.',
            'gender.in' => 'giới tính không hợp lệ.',
            'date_of_birth.before' => 'Ngày sinh phải trước ngày hiện tại.',
            'weight.numeric' => 'cân nặng phải là số.',
        ];
    }
}
