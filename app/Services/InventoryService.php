<?php

namespace App\Services;

use App\Models\Medicine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    /**
     * Trừ tồn kho thuốc theo nguyên tắc FEFO (First Expired, First Out - Lô nào hết hạn trước xuất trước).
     * Ném ra ValidationException nếu không đủ số lượng tồn kho.
     */
    public function deductStock(int $medicineId, int $quantity): Medicine
    {
        // lockForUpdate: chống race condition khi nhiều bác sĩ cùng kê đơn đồng thời
        $medicine = Medicine::lockForUpdate()->findOrFail($medicineId);

        if ($medicine->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'prescriptions' => "Thuốc \"{$medicine->name}\" không đủ tồn kho (hiện còn {$medicine->stock_quantity} {$medicine->unit}, yêu cầu {$quantity})."
            ]);
        }

        // Trừ lần lượt từ các lô có hạn dùng gần nhất (FEFO)
        $needToDeduct = $quantity;
        $batches = $medicine->batches()
            ->where('remaining_quantity', '>', 0)
            ->orderByRaw('expiry_date IS NULL, expiry_date ASC, id ASC')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($needToDeduct <= 0) {
                break;
            }

            if ($batch->remaining_quantity >= $needToDeduct) {
                $batch->decrement('remaining_quantity', $needToDeduct);
                $needToDeduct = 0;
            } else {
                $deductedFromBatch = $batch->remaining_quantity;
                $batch->update(['remaining_quantity' => 0]);
                $needToDeduct -= $deductedFromBatch;
            }
        }

        // Tự động tính toán lại tổng tồn kho và hạn sử dụng gần nhất của thuốc
        $medicine->recalculateStockAndEarliestExpiry();
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
        $medicine = Medicine::lockForUpdate()->findOrFail($medicineId);

        // Hoàn trả vào lô gần nhất còn hoạt động
        $latestBatch = $medicine->batches()->latest('id')->first();
        if ($latestBatch) {
            $latestBatch->increment('remaining_quantity', $quantity);
        } else {
            $medicine->batches()->create([
                'batch_number'       => 'LO-RETURN-' . date('Ymd'),
                'quantity'           => $quantity,
                'remaining_quantity' => $quantity,
                'import_price'       => $medicine->import_price,
                'expiry_date'        => $medicine->expiry_date,
            ]);
        }

        $medicine->recalculateStockAndEarliestExpiry();
        $medicine->refresh();

        return $medicine;
    }
}
