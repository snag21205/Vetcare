<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicineRequest extends FormRequest
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
            'stock_quantity'      => ['required', 'integer', 'min:0'],
            'import_price'        => ['required', 'numeric', 'min:0'],
            'sell_price'          => ['required', 'numeric', 'min:0', 'gte:import_price'],
            'expiry_date'         => ['nullable', 'date'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                => 'Tên thuốc không được để trống.',
            'unit.required'                => 'Đơn vị tính không được để trống.',
            'stock_quantity.required'      => 'Số lượng tồn kho không được để trống.',
            'import_price.required'        => 'Giá nhập không được để trống.',
            'sell_price.required'          => 'Giá bán không được để trống.',
            'sell_price.gte'               => 'Giá bán phải lớn hơn hoặc bằng giá nhập.',
            'low_stock_threshold.required' => 'Ngưỡng cảnh báo tồn kho không được để trống.',
        ];
    }
}
