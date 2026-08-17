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
