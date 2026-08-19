<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        // $this->route('customer') trả về Customer model (Route Model Binding)
        // ->getKey() lấy ID của nó
        $customerId = $this->route('customer')?->getKey();

        return [
            'name'    => 'required|string|max:255',
            // ignore($customerId): bỏ qua chính record này khi check unique
            // whereNull('deleted_at'): bỏ qua soft-deleted records
            'phone'   => ['required', 'string', 'max:20',
                          Rule::unique('customers', 'phone')
                              ->ignore($customerId)
                              ->whereNull('deleted_at')],
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'note'    => 'nullable|string',
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
