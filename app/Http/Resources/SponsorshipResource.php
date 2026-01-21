<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SponsorshipResource extends JsonResource
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
            'monthly_amount' => (float) $this->monthly_amount,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status,
            'payment_frequency' => $this->payment_frequency,
            'duration_months' => $this->duration_months,
            'total_paid' => (float) $this->total_paid,
            'is_active' => $this->is_active,
            'next_payment_date' => $this->next_payment_date?->format('Y-m-d'),
            'notes' => $this->notes,
            'user' => new UserResource($this->whenLoaded('user')),
            'family' => new FamilyResource($this->whenLoaded('family')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}