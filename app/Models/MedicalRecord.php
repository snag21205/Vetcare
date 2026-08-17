<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MedicalRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'appointment_id',
        'pet_id',
        'doctor_id',
        'symptoms',
        'diagnosis',
        'weight_at_visit',
        'temperature_at_visit',
        'note',
        'status',
    ];

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'medical_record_services')
            ->withPivot('price')
            ->withTimestamps();
    }
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
