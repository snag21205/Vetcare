<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
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
        $customerId = $this->route('customer');

        return [
            'name' => 'required|string|max:255',
            'phone' => "required|string|max:20|unique:customers,phone,{$customerId}",
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'note' => 'nullable|string',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên khách hàng',
            'phone.required' => 'Vui lòng nhập số điện thoại',
            'phone.unique' => 'Số điện thoại đã tồn tại trong hệ thống',

        ];
    }
}
