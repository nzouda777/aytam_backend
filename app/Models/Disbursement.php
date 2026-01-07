<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'family_id',
        'processed_by',
        'amount',
        'disbursement_date',
        'type',
        'description',
        'receipt_number',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'disbursement_date' => 'date',
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($disbursement) {
            if (empty($disbursement->receipt_number)) {
                $disbursement->receipt_number = 'RCP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            }
        });

        // Mettre à jour le total reçu par la famille
        static::created(function ($disbursement) {
            $family = $disbursement->family;
            $family->total_received += $disbursement->amount;
            $family->save();
        });
    }

    // Relations
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopeCash($query)
    {
        return $query->where('type', 'cash');
    }

    public function scopeGoods($query)
    {
        return $query->where('type', 'goods');
    }

    public function scopeServices($query)
    {
        return $query->where('type', 'services');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('disbursement_date', now()->month)
                     ->whereYear('disbursement_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('disbursement_date', now()->year);
    }
}