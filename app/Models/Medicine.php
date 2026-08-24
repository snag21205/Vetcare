<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'unit',
        'stock_quantity',
        'import_price',
        'sell_price',
        'expiry_date',
        'low_stock_threshold'
    ];
    protected $casts = [
        'expiry_date' => 'date',
        'import_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
    ];

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class);
    }

    /**
     * Tự động tính lại tổng tồn kho và hạn sử dụng gần nhất từ các lô còn thuốc.
     */
    public function recalculateStockAndEarliestExpiry(): self
    {
        $totalStock = (int)$this->batches()->sum('remaining_quantity');
        
        $earliestExpiry = $this->batches()
            ->where('remaining_quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->min('expiry_date');

        $this->stock_quantity = $totalStock;
        $this->expiry_date = $earliestExpiry;
        $this->save();

        return $this;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date) return false;
        return now()->diffInDays($this->expiry_date, false) <= 30;
    }
}
