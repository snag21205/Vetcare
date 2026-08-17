<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $fillable = [
        'pet_id',
        'doctor_id',
        'created_by',
        'scheduled_at',
        'status',
        'reason',
        'note',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }
}
