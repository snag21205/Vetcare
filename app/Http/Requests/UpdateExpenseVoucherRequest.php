<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category'     => ['required', 'in:medicine_import,operational,other'],
            'amount'       => ['required', 'numeric', 'min:1000'],
            'description'  => ['required', 'string', 'max:1000'],
            'voucher_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required'     => 'Danh mục chi không được để trống.',
            'category.in'           => 'Danh mục chi không hợp lệ.',
            'amount.required'       => 'Số tiền chi không được để trống.',
            'amount.min'            => 'Số tiền chi tối thiểu là 1.000 đ.',
            'description.required'  => 'Lý do chi không được để trống.',
            'voucher_date.required' => 'Ngày lập phiếu chi không được để trống.',
        ];
    }
}
