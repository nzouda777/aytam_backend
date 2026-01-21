<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DonationResource extends JsonResource
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
            'transaction_id' => $this->transaction_id,
            'amount' => (float) $this->amount,
            'donor_name' => $this->donor_display_name,
            'donor_email' => $this->is_anonymous ? null : $this->donor_email,
            'donor_phone' => $this->is_anonymous ? null : $this->donor_phone,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'is_anonymous' => $this->is_anonymous,
            'is_recurring' => $this->is_recurring,
            'is_successful' => $this->is_successful,
            'message' => $this->message,
            'payment_date' => $this->payment_date?->format('Y-m-d H:i:s'),
            'user' => new UserResource($this->whenLoaded('user')),
            'campaign' => new CampaignResource($this->whenLoaded('campaign')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}