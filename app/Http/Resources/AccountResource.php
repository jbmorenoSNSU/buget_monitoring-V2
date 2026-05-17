<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_type_id' => $this->account_type_id,
            'account_type' => $this->whenLoaded('accountType', fn () => [
                'id' => $this->accountType->id,
                'name' => $this->accountType->name,
                'icon' => $this->accountType->icon,
            ]),
            'person_id' => $this->person_id,
            'person' => $this->whenLoaded('person', fn () => [
                'id' => $this->person->id,
                'name' => $this->person->name,
                'color' => $this->person->color,
            ]),
            'name' => $this->name,
            'description' => $this->description,
            'initial_balance' => (float) $this->initial_balance,
            'current_balance' => (float) $this->current_balance,
            'color' => $this->relationLoaded('person') && $this->person ? $this->person->color : '#94A3B8',
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
