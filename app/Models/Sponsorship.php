<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sponsorship extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'sponsorship_type',
        'family_id',
        'orphan_id',
        'name',
        'monthly_amount',
        'start_date',
        'end_date',
        'status',
        'payment_frequency',
        'notes',
    ];

    protected $casts = [
        'monthly_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaused($query)
    {
        return $query->where('status', 'paused');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Accessors
    public function getDurationMonthsAttribute()
    {
        if (!$this->start_date) return 0;
        
        $endDate = $this->end_date ?? now();
        return $this->start_date->diffInMonths($endDate);
    }

    public function getTotalPaidAttribute()
    {
        return $this->duration_months * $this->monthly_amount;
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active' && 
               $this->start_date <= now() && 
               (!$this->end_date || $this->end_date >= now());
    }

    public function getNextPaymentDateAttribute()
    {
        if ($this->status !== 'active') return null;

        $lastPayment = $this->start_date;
        
        switch ($this->payment_frequency) {
            case 'monthly':
                return $lastPayment->addMonth();
            case 'quarterly':
                return $lastPayment->addMonths(3);
            case 'yearly':
                return $lastPayment->addYear();
            default:
                return null;
        }
    }
   public function orphan()
   {
       return $this->belongsTo(Orphan::class);
   }
}