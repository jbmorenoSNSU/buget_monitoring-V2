<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:0.01|max:9999999999999.99',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'reference_number' => 'nullable|string|max:100',

            // Conditional fields — Laravel strips these from validated() when excluded
            'category_id' => 'exclude_if:type,transfer|nullable|exists:categories,id',
            'transfer_to_account_id' => 'exclude_unless:type,transfer|required|exists:accounts,id|different:account_id',
            'debt_id' => 'exclude_unless:type,expense|nullable|integer|exists:debts,id',
        ];
    }
}
