<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavingsGoalResource extends JsonResource
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
            'account_id' => $this->account_id,
            'person_id' => $this->person_id,
            'name' => $this->name,
            'target_amount' => (float) $this->target_amount,
            'current_amount' => (float) $this->current_amount,
            'target_date' => $this->target_date ? $this->target_date->format('Y-m-d') : null,
            'percent' => $this->percent ?? 0,
            'remaining_amount' => $this->remaining_amount ?? 0,
            'is_completed' => $this->is_completed ?? false,
            'days_remaining' => $this->days_remaining,
            'account' => $this->whenLoaded('account'),
            'person' => $this->whenLoaded('person'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
