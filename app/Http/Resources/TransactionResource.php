<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'account' => $this->whenLoaded('account', fn () => [
                'id' => $this->account->id,
                'name' => $this->account->name,
                'color' => $this->account->color,
            ]),
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'icon' => $this->category->icon,
                'color' => $this->category->color,
            ]),
            'type' => $this->type->value ?? $this->type,
            'amount' => (float) $this->amount,
            'transaction_date' => $this->transaction_date?->format('Y-m-d'),
            'description' => $this->description,
            'notes' => $this->notes,
            'reference_number' => $this->reference_number,
            'transfer_to_account_id' => $this->transfer_to_account_id,
            'transfer_to_account' => $this->whenLoaded('transferToAccount', fn () => [
                'id' => $this->transferToAccount->id,
                'name' => $this->transferToAccount->name,
            ]),
            'recurring_id' => $this->recurring_id,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
