<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'medical_record_id',
        'customer_id',
        'invoice_number',
        'subtotal_exam',
        'subtotal_medicine',
        'subtotal_service',
        'total_amount',
        'status',
        'paid_at',
        'created_by',
    ];
    protected $casts = [
        'paid_at' => 'datetime'
    ];
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
