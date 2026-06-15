<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebtResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'person_id' => $this->person_id,
            'person' => $this->whenLoaded('person', fn () => [
                'id' => $this->person->id,
                'name' => $this->person->name,
                'color' => $this->person->color,
            ]),
            'name' => $this->name,
            'principal_amount' => (float) $this->principal_amount,
            'interest_rate' => (float) $this->interest_rate,
            'minimum_payment' => (float) $this->minimum_payment,
            'due_date_day' => $this->due_date_day,
            'status' => $this->status,
            'payoff_projection' => $this->payoff_projection ?? null,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
