<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Donation;
use App\Models\Disbursement;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'goal_amount',
        'current_amount',
        'start_date',
        'end_date',
        'status',
        'urgency',
        'image',
        'beneficiaries_count',
        'is_featured',
    ];

    protected $casts = [
        'goal_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_featured' => 'boolean',
        'beneficiaries_count' => 'integer',
    ];

    protected $appends = ['image_url'];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($campaign) {
            if (empty($campaign->slug)) {
                $campaign->slug = Str::slug($campaign->title);
            }
        });
    }

    // Relations
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
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

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeUrgent($query)
    {
        return $query->where('urgency', 'urgent');
    }

    // Accessors
    public function getProgressPercentageAttribute()
    {
        if ($this->goal_amount == 0) return 0;
        return min(100, round(($this->current_amount / $this->goal_amount) * 100, 2));
    }

    public function getRemainingAmountAttribute()
    {
        return max(0, $this->goal_amount - $this->current_amount);
    }

    public function getDonorsCountAttribute()
    {
        return $this->donations()->where('status', 'completed')->distinct('user_id')->count('user_id');
    }

    public function getIsExpiredAttribute()
    {
        return $this->end_date < now();
    }

    public function getDaysRemainingAttribute()
    {
        if ($this->is_expired) return 0;
        return now()->diffInDays($this->end_date);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        return filter_var($this->image, FILTER_VALIDATE_URL) 
            ? $this->image 
            : asset('storage/' . $this->image);
    }
}