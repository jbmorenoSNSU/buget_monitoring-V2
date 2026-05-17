<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'accounts_count' => (int) ($this->accounts_count ?? 0),
            'total_balance' => (float) ($this->accounts_sum_current_balance ?? 0),
            'income_this_month' => (float) ($this->income_this_month ?? 0),
            'expense_this_month' => (float) ($this->expense_this_month ?? 0),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
