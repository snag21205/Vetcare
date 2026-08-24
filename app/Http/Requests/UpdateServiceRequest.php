<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'price'       => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Tên dịch vụ không được để trống.',
            'price.required' => 'Đơn giá dịch vụ không được để trống.',
            'price.min'      => 'Đơn giá dịch vụ phải từ 0 đ trở lên.',
        ];
    }
}
