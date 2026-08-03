<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecurringTransactionResource extends JsonResource
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
            'category_id' => $this->category_id,
            'debt_id' => $this->debt_id,
            'type' => $this->type->value ?? $this->type,
            'amount' => (float) $this->amount,
            'advance_credit' => (float) ($this->advance_credit ?? 0),
            'effective_amount' => max(0, (float) $this->amount - (float) ($this->advance_credit ?? 0)),
            'description' => $this->description,
            'frequency' => $this->frequency->value ?? $this->frequency,
            'start_date' => $this->start_date ? $this->start_date->format('Y-m-d') : null,
            'end_date' => $this->end_date ? $this->end_date->format('Y-m-d') : null,
            'next_due_date' => $this->next_due_date ? $this->next_due_date->format('Y-m-d') : null,
            'last_generated_date' => $this->last_generated_date ? $this->last_generated_date->format('Y-m-d') : null,
            'is_active' => (bool) $this->is_active,
            'account' => $this->whenLoaded('account'),
            'category' => $this->whenLoaded('category'),
            'debt' => $this->whenLoaded('debt'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
