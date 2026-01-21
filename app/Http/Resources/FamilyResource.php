<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FamilyResource extends JsonResource
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
            'family_code' => $this->family_code,
            'widow_name' => $this->widow_name,
            'widow_phone' => $this->widow_phone,
            'widow_email' => $this->widow_email,
            'widow_date_of_birth' => $this->widow_date_of_birth->format('Y-m-d'),
            'widow_age' => $this->widow_age,
            'address' => $this->address,
            'city' => $this->city,
            'region' => $this->region,
            'orphans_count' => $this->orphans_count,
            'status' => $this->status,
            'registration_date' => $this->registration_date->format('Y-m-d'),
            'notes' => $this->notes,
            'total_received' => (float) $this->total_received,
            'monthly_income' => (float) $this->monthly_income,
            'total_disbursements' => (float) $this->total_disbursements,
            'orphans' => OrphanResource::collection($this->whenLoaded('orphans')),
            'sponsorships' => SponsorshipResource::collection($this->whenLoaded('sponsorships')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}