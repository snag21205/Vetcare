<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pet extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'customer_id',
        'name',
        'species',
        'breed',
        'date_of_birth',
        'gender',
        'weight',
        'avatar_path',
        'note',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'weight' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function appoinments(): HasMany
    {
        return $this->appointments();
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function medicalRecord(): HasMany
    {
        return $this->medicalRecords();
    }
}
