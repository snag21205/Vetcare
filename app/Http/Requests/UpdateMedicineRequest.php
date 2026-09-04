<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'unit'                => ['required', 'string', 'max:50'],
            'stock_quantity'      => ['nullable', 'integer', 'min:0'],
            'import_price'        => ['required', 'numeric', 'min:0'],
            'sell_price'          => ['required', 'numeric', 'min:0', 'gte:import_price'],
            'expiry_date'         => ['nullable', 'date'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'batches'             => ['nullable', 'array'],
            'batches.*.id'        => ['nullable', 'integer', 'exists:medicine_batches,id'],
            'batches.*.batch_number' => ['nullable', 'string', 'max:100'],
            'batches.*.remaining_quantity' => ['required', 'integer', 'min:0'],
            'batches.*.quantity'  => ['nullable', 'integer', 'min:0'],
            'batches.*.import_price' => ['nullable', 'numeric', 'min:0'],
            'batches.*.expiry_date' => ['nullable', 'date'],
            'batches.*._delete'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                => 'Tên thuốc không được để trống.',
            'unit.required'                => 'Đơn vị tính không được để trống.',
            'import_price.required'        => 'Giá nhập không được để trống.',
            'sell_price.required'          => 'Giá bán không được để trống.',
            'sell_price.gte'               => 'Giá bán phải lớn hơn hoặc bằng giá nhập.',
            'low_stock_threshold.required' => 'Ngưỡng cảnh báo tồn kho không được để trống.',
            'batches.*.remaining_quantity.required' => 'Số lượng tồn của lô không được để trống.',
            'batches.*.remaining_quantity.min'      => 'Số lượng tồn của lô phải lớn hơn hoặc bằng 0.',
        ];
    }
}
