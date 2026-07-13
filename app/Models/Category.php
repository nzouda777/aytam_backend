<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasLocalizedTranslations;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, HasLocalizedTranslations;
    /** @var array<int, string> attributs traduits (JSON fr/en) */
    public array $translatable = ['name', 'description'];


    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relations
    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors
    public function getActiveCampaignsCountAttribute()
    {
        return $this->campaigns()->where('status', 'active')->count();
    }

    public function getTotalRaisedAttribute()
    {
        return $this->campaigns()->sum('current_amount');
    }
}