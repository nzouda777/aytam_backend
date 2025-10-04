<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orphan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'family_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'school_name',
        'school_level',
        'health_status',
        'special_needs',
        'photo',
        'is_sponsored',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_sponsored' => 'boolean',
    ];

    // Relations
    public function family()
    {
        return $this->belongsTo(Family::class);
    }

    // Scopes
    public function scopeSponsored($query)
    {
        return $query->where('is_sponsored', true);
    }

    public function scopeUnsponsored($query)
    {
        return $query->where('is_sponsored', false);
    }

    public function scopeMale($query)
    {
        return $query->where('gender', 'male');
    }

    public function scopeFemale($query)
    {
        return $query->where('gender', 'female');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getIsSchoolAgeAttribute()
    {
        $age = $this->age;
        return $age >= 5 && $age <= 18;
    }
}