<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrphanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'family_id' => $this->family_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'date_of_birth' => $this->date_of_birth->format('Y-m-d'),
            'age' => $this->age,
            'gender' => $this->gender,
            'school_name' => $this->school_name,
            'school_level' => $this->school_level,
            'health_status' => $this->health_status,
            'special_needs' => $this->special_needs,
            'photo' => $this->photo ? asset('storage/' . $this->photo) : null,
            'is_sponsored' => $this->is_sponsored,
            'is_school_age' => $this->is_school_age,
            'family' => new FamilyResource($this->whenLoaded('family')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}