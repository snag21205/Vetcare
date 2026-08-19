<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
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
            'name'    => ['required', 'string', 'max:255'],
            // Rule::unique() builder: bỏ qua bản ghi soft-deleted khi check trùng SĐT
            'phone'   => ['required', 'string', 'max:20',
                          Rule::unique('customers', 'phone')->whereNull('deleted_at')],
            'email'   => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'note'    => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên khách hàng,',
            'phone.required' => 'Vui lòng nhập số điện thoại,',
            'phone.unique' => 'Số điện thoại nầy đã được sử dụng trong hệ thống.',
            'email.email' => 'Định dạng Email không hợp lệ',

        ];
    }
}
