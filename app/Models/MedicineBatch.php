<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'batch_number',
        'quantity',
        'remaining_quantity',
        'import_price',
        'expiry_date',
    ];

    protected $casts = [
        'quantity'           => 'integer',
        'remaining_quantity' => 'integer',
        'import_price'       => 'decimal:2',
        'expiry_date'        => 'date',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
