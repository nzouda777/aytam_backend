<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Family extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'family_code',
        'widow_name',
        'widow_phone',
        'widow_email',
        'widow_date_of_birth',
        'address',
        'city',
        'region',
        'orphans_count',
        'status',
        'registration_date',
        'notes',
        'total_received',
    ];

    protected $casts = [
        'widow_date_of_birth' => 'date',
        'registration_date' => 'date',
        'orphans_count' => 'integer',
        'total_received' => 'decimal:2',
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($family) {
            if (empty($family->family_code)) {
                $family->family_code = 'FAM-' . strtoupper(uniqid());
            }
        });
    }

    // Relations
    public function orphans()
    {
        return $this->hasMany(Orphan::class);
    }

    public function sponsorships()
    {
        return $this->hasMany(Sponsorship::class);
    }

    public function disbursements()
    {
        return $this->hasMany(Disbursement::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Accessors
    public function getWidowAgeAttribute()
    {
        return $this->widow_date_of_birth ? $this->widow_date_of_birth->age : null;
    }

    public function getActiveSponsorshipsAttribute()
    {
        return $this->sponsorships()->where('status', 'active')->get();
    }

    public function getMonthlyIncomeAttribute()
    {
        return $this->sponsorships()
            ->where('status', 'active')
            ->where('payment_frequency', 'monthly')
            ->sum('monthly_amount');
    }

    public function getTotalDisbursementsAttribute()
    {
        return $this->disbursements()->sum('amount');
    }
}