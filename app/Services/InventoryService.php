<?php

namespace App\Services;

use App\Models\Medicine;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    /**
     * Trừ tồn kho thuốc. Ném ra ValidationException nếu không đủ số lượng tồn kho.
     */
    public function deductStock(int $medicineId, int $quantity): Medicine
    {
        // lockForUpdate: chống race condition khi nhiều người cùng kê đơn 1 lúc
        $medicine = Medicine::lockForUpdate()->findOrFail($medicineId);

        if ($medicine->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'prescriptions' => "Thuốc \"{$medicine->name}\" không đủ tồn kho (hiện còn {$medicine->stock_quantity}, yêu cầu {$quantity})."
            ]);
        }

        $medicine->decrement('stock_quantity', $quantity);
        $medicine->refresh();

        // Gửi thông báo cho Admin nếu tồn kho xuống dưới hoặc bằng ngưỡng
        if ($medicine->isLowStock()) {
            $admins = \App\Models\User::role('admin')->get();
            if ($admins->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\LowStockNotification($medicine));
            }
        }

        return $medicine;
    }

    /**
     * Hoàn trả tồn kho thuốc (khi hủy hoặc sửa đơn thuốc).
     */
    public function returnStock(int $medicineId, int $quantity): Medicine
    {
        $medicine = Medicine::findOrFail($medicineId);
        $medicine->increment('stock_quantity', $quantity);

        return $medicine;
    }
}
