<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory, HasLocalizedTranslations;

    /** @var array<int, string> attributs traduits (JSON fr/en) */
    public array $translatable = ['title', 'excerpt', 'content'];

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'content',
        'icon',
        'image',
        'category_id',
        'cta_type',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
