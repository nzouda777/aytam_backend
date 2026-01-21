<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'goal_amount' => (float) $this->goal_amount,
            'current_amount' => (float) $this->current_amount,
            'remaining_amount' => (float) $this->remaining_amount,
            'progress_percentage' => $this->progress_percentage,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'days_remaining' => $this->days_remaining,
            'status' => $this->status,
            'urgency' => $this->urgency,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'beneficiaries_count' => $this->beneficiaries_count,
            'is_featured' => $this->is_featured,
            'is_expired' => $this->is_expired,
            'donors_count' => $this->donors_count,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'donations' => DonationResource::collection($this->whenLoaded('donations')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}