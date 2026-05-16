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
            'name' => $this->name,
            'description' => $this->description,
            'initial_balance' => (float) $this->initial_balance,
            'current_balance' => (float) $this->current_balance,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
