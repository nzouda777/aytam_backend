<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'active_campaigns_count' => $this->active_campaigns_count,
            'total_raised' => (float) $this->total_raised,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}