<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'campaign_id',
        'transaction_id',
        'amount',
        'donor_name',
        'donor_email',
        'donor_phone',
        'payment_method',
        'status',
        'is_anonymous',
        'is_recurring',
        'message',
        'payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_anonymous' => 'boolean',
        'is_recurring' => 'boolean',
        'payment_date' => 'datetime',
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($donation) {
            if (empty($donation->transaction_id)) {
                $donation->transaction_id = 'TXN-' . strtoupper(uniqid());
            }
        });

        // Mettre à jour le montant de la campagne quand le don est complété
        static::updated(function ($donation) {
            if ($donation->status === 'completed' && $donation->campaign_id) {
                $campaign = $donation->campaign;
                $campaign->current_amount += $donation->amount;
                $campaign->save();
            }
        });
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeAnonymous($query)
    {
        return $query->where('is_anonymous', true);
    }

    // Accessors
    public function getDonorDisplayNameAttribute()
    {
        if ($this->is_anonymous) {
            return 'Donateur Anonyme';
        }
        return $this->donor_name ?? $this->user?->name ?? 'Donateur';
    }

    public function getIsSuccessfulAttribute()
    {
        return $this->status === 'completed';
    }
