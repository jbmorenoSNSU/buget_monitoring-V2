<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetGoalResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'icon' => $this->category->icon,
                'color' => $this->category->color,
            ]),
            'month' => $this->month,
            'year' => $this->year,
            'limit_amount' => (float) $this->limit_amount,
            'spent' => (float) ($this->spent ?? 0),
            'remaining' => (float) ($this->remaining ?? $this->limit_amount),
            'percent' => (float) ($this->percent ?? 0),
            'status' => $this->status ?? 'safe',
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
