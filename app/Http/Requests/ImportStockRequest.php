<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity'       => ['required', 'integer', 'min:1'],
            'import_price'   => ['required', 'numeric', 'min:0'],
            'batch_number'   => ['nullable', 'string', 'max:50'],
            'create_voucher' => ['boolean'],
            'expiry_date'    => ['nullable', 'date'],
            'note'           => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required'     => 'Số lượng nhập không được để trống.',
            'quantity.min'          => 'Số lượng nhập phải từ 1 trở lên.',
            'import_price.required' => 'Giá nhập không được để trống.',
        ];
    }
}
