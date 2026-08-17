<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseVoucher extends Model
{
    protected $fillable = [
        'category',
        'amount',
        'description',
        'voucher_date',
        'created_by',
    ];
    protected $casts = [
        'voucher_date' => 'date',
        'amount'       => 'decimal:2',
    ];
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
