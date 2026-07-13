<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory, HasLocalizedTranslations;

    /** @var array<int, string> attributs traduits (JSON fr/en) */
    public array $translatable = ['role', 'quote'];

    protected $fillable = [
        'name',
        'role',
        'quote',
        'avatar',
        'rating',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
